/**
 * Copyright 2017 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

(function() {
  "use strict";

  var ENTER_KEY_CODE = 13;
  var queryInput, resultDiv, accessTokenInput;

  window.onload = init;

  function init() {
    queryInput = document.getElementById("q");
    resultDiv = document.getElementById("result");
    setAccessToken();

    queryInput.addEventListener("keydown", queryInputKeyDown);
    var responseNode = createResponseNode();
    setTimeout(function(){
      setResponseOnNode("Willkommen im Kleinwalsertal! Wie kann ich dir bei deiner Suche nach Unterkünften behilflich sein?", responseNode);
      queryInput.style.display = "block";
    }, 1000);
    // setAccessTokenButton.addEventListener("click", setAccessToken);
  }

  function setAccessToken() {
    window.init("32a72777f2ec4d16b017151f2938cc2d");
  }

  function queryInputKeyDown(event) {
    if (event.which !== ENTER_KEY_CODE) {
      return;
    }

    var value = queryInput.value;
    queryInput.value = "";

    createQueryNode(value);
    var responseNode = createResponseNode();

    sendText(value)
        .then(function(response) {
          var result;
          try {
            if (response.result.fulfillment.speech == "hotellist") {

              console.log("response hotellist");
              result = response.result.fulfillment.data.text;
              setResponseJSON(response);
              setResponseOnNode(response.result.fulfillment.data.text, responseNode);
              hotelListOnNode(response.result.fulfillment.data.attachments, createResponseNode());
              setResponseOnNode(response.result.fulfillment.data.selection, createResponseNode());

            } else if (response.result.fulfillment.speech == "nohotels") {
              result = response.result.fulfillment.data.text;
              setResponseJSON(response);
              setResponseOnNode(response.result.fulfillment.data.text, responseNode);
              setResponseOnNode(response.result.fulfillment.data.selection, createResponseNode());
              if (response.result.fulfillment.data.attachments) { // ich habe substitutes gefunden
                console.log("new params",response.result.fulfillment.data.newParams);
                triggerEventWithParams("substitutes", response.result.fulfillment.data.newParams).then(function (eventResponse) {
                  console.log("adapting the parameters",eventResponse);
                  setResponseOnNode(response.result.fulfillment.data.newText, createResponseNode());
                  setResponseOnNode(response.result.fulfillment.data.newSelection, createResponseNode());
                }).catch(function (error) {
                  console.log(error);
                });
              }
            } else if (response.result.fulfillment.speech == "enoughhotels") {
              setResponseJSON(response);
              setResponseOnNode(response.result.fulfillment.data.text, responseNode);
              setResponseOnNode(response.result.fulfillment.data.selection, createResponseNode());
            } else if (response.result.fulfillment.speech == "toomanyhotels" ) {
              setResponseJSON(response);
              setResponseOnNode(response.result.fulfillment.data.text, responseNode);
              setResponseOnNode(response.result.fulfillment.data.selection, createResponseNode());
            } else if (response.result.fulfillment.speech == "checksorting") {
              setResponseOnNode(response.result.fulfillment.data.text, responseNode);
              console.log("immer noch zu viele hotels - sortierung abfragen");
              triggerEvent("sorting").then(function (response) {
                console.log("sorting result");
                setResponseJSON(response);
                setResponseOnNode(response.result.fulfillment.data.text, responseNode);
                if (response.result.fulfillment.speech == "hotellist") {
                  hotelListOnNode(response.result.fulfillment.data.attachments, responseNode);
                }
              }).catch(function (error) {
                console.log(error);
              });
            } else if (response.result.fulfillment.speech == "morepictures") {
              console.log("bilder");
              setResponseJSON(response);
              // setResponseOnNode(response.result.fulfillment.speech, responseNode);
              if (response.result.fulfillment.speech == "morepictures") {
                imageListOnNode(response.result.fulfillment.data.attachments, responseNode);
              }

            } else if (response.result.fulfillment.speech == "nightsperiod") {
              console.log("user is flexible - ask for nights period");
              setResponseOnNode(response.result.fulfillment.data.text, responseNode);
              setTimeout(function () {
                setResponseOnNode(response.result.fulfillment.data.options, createResponseNode());
              }, 1000);

            } else if (response.result.fulfillment.speech == "showevents") {
              console.log("show events");
              setResponseLinkOnNode(response, responseNode);

            } else {
              console.log("sonstiges");
              console.log("action", response.result.action);
              result = response.result.fulfillment.speech;
              setResponseJSON(response);
              setResponseOnNode(result, responseNode);
            }
          } catch(error) {
            result = "";
          }
          // setResponseJSON(response);
          // setResponseOnNode(result, responseNode);
        })
        .catch(function(err) {
          setResponseJSON(err);
          setResponseOnNode("Something goes wrong", responseNode);
        });
  }

  function createQueryNode(query) {
    var node = document.createElement('div');
    node.className = "clearfix right-align right chatbot-text filter-navigation";
    node.innerHTML = query;
    resultDiv.appendChild(node);
  }

  function createResponseNode() {
    var wrapper = document.createElement('div');
    wrapper.className = "chatbot-text-wrapper clearfix";
    var node = document.createElement('div');
    node.className = "chatbot-icon";
    wrapper.appendChild(node);
    var textNode = document.createElement('div');
    textNode.className = "left-align left chatbot-text filter-navigation";
    textNode.innerHTML = "...";
    wrapper.appendChild(textNode);
    resultDiv.appendChild(wrapper);
    return textNode;
  }

  function setResponseOnNode(response, node) {
    node.innerHTML = response ? response : "[empty response]";
    node.setAttribute('data-actual-response', response);
    scrollToMessage();
  }
  function setResponseLinkOnNode(response, node) {
    node.innerHTML = response.result.fulfillment.data.text + "\n";
    var link = document.createElement('a');
    link.className = "btn btn-success btn-radius-2";
    link.href = response.result.fulfillment.data.url;
    link.innerHTML = "anzeigen";
    link.target = "_blank";
    node.append(link);
    var download = document.createElement('a');
    download.className = "btn btn-success btn-radius-2";
    // link.href = response.url;
    download.innerHTML = "als PDF herunterladen";
    download.target = "_blank";
    node.append(download);
    scrollToMessage();
  }

  function hotelListOnNode(response,node) {
    node.innerHTML = "";
    var slick = document.createElement('div');
    slick.className = "detail-slick col-sm-12";
    var length = response.length;
    $.each( response, function( key, value ) {
      var child = document.createElement('div');
      child.className = length == 1 ? "col-sm-12 margin-bottom-20 content-box " : "col-sm-6 margin-bottom-20 content-box ";
      var headline = document.createElement('h4');
      headline.innerHTML = value.title + " " + value.classification;
      child.appendChild(headline);
      var text = document.createElement('p');
      text.innerHTML = value.type;
      child.appendChild(text);
      var address = document.createElement('p');
      address.innerHTML = "in " +value.address;
      child.appendChild(address);
      var description = document.createElement('p');
      description.className = "bold";
      description.innerHTML = value.description;
      var price = document.createElement('p');
      price.className = "bold";
      price.innerHTML = value.price;
      child.appendChild(description);
      child.appendChild(price);
      var details = document.createElement('a');
      details.className = "btn btn-success btn-radius-2";
      details.href = value.detail_url;
      details.innerHTML = "Details";
      details.target = "_blank";
      // var fotos = document.createElement('a');
      // fotos.className = "btn btn-success btn-radius-2";
      // fotos.innerHTML("Fotos");
      // fotos.onclick = function(){
      //   sende foto anfrage!
      // console.log(value);
      // sendText("Zeig mir mehr Bilder");
      // };

      var responsive = document.createElement('div');
      responsive.className = "embed-responsive embed-responsive-3by2";
      var image = document.createElement('img');
      image.className = "embed-responsive-item";
      image.src = value.image_url;
      responsive.appendChild(image);
      child.appendChild(responsive);
      // child.appendChild(fotos);
      child.appendChild(details);
      slick.appendChild(child);

      node.appendChild(slick);
      console.log( value );
    });
    node.appendChild(slick);
    // scrollToMessage();
  }



  function imageListOnNode(response, node) {
    node.innerHTML = "";
    node.className = "col-sm-12 left-align left chatbot-text filter-navigation images";
    var slickNode = document.createElement('div');
    slickNode.className = " col-sm-12";
    $.each( response, function( key, value ) {
      if (key < 10) {
        // var slick = document.createElement('div');
        //   slick.className = "detail-slick__item";
        var embed = document.createElement('div');
        embed.className = "image-gallery";
        var image = document.createElement('img');
        image.title = value['title'];
        image.setAttribute('src',value['link']);
        image.className = "embed-responsive-item";
        image.height = 200;
        embed.append(image);
        // slick.append(embed);
        slickNode.appendChild(embed);
      }
    });
    node.appendChild(slickNode);
    //
    // $.getScript('/static/demi2015/js/libs/slick.min.js').done(function () {
    //   $('.detail-slick').slick({
    //     lazyLoad: 'progressive',
    //     mobileFirst: true,
    //     slidesToShow: 1,
    //     slidesToScroll: 1
    //   });
    // });
    scrollToMessage();
  }

  function setResponseJSON(response) {
    // var node = document.getElementById("jsonResponse");
    // node.innerHTML = JSON.stringify(response, null, 2);
    console.log(response);
    // console.log(JSON.stringify(response, null, 2));
  }

  function sendRequest() {

  }


  function scrollToMessage() {
    if ($('#result-wrapper').height() > 600) {
      $('html, body').animate({
        scrollTop: $("#q").offset().top - 300
      }, 2000);
    }
  }

})();