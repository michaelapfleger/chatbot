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

      setResponseOnNode("Willkommen im Kleinwalsertal! Wie kann ich dir behilflich sein?", responseNode);
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
              setResponseOnNode(response.result.fulfillment.data.pretext, responseNode);
              hotelListOnNode(response.result.fulfillment.data.attachments, responseNode);
            } else if (response.result.fulfillment.speech == "toomanyhotels" ) {
              console.log("too many hotels");
              triggerEvent("stars-unterkunft").then(function (response) {
                setResponseJSON(response);
                setResponseOnNode(response.result.fulfillment.speech, responseNode);
                if (response.result.fulfillment.speech == "hotellist") {
                  hotelListOnNode(response.result.fulfillment.data.attachments, responseNode);
                }
              }).catch(function (error) {
                console.log(error);
              });
            } else if (response.result.fulfillment.speech == "toomanyhotelsafterstars") {
              console.log("too many hotels, better check board");
              triggerEvent("board").then(function (response) {
                console.log("board result");
                setResponseJSON(response);
                setResponseOnNode(response.result.fulfillment.speech, responseNode);
                if (response.result.fulfillment.speech == "hotellist") {
                  hotelListOnNode(response.result.fulfillment.data.attachments, responseNode);
                }
              }).catch(function (error) {
                console.log(error);
              });

            } else if (response.result.fulfillment.speech == "toomanyhotelsafterboard") {
              console.log("too many hotels, better check interests");
              triggerEvent("interests").then(function (response) {
                console.log("interests result");
                setResponseJSON(response);
                setResponseOnNode(response.result.fulfillment.speech, responseNode);
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

            } else {
              console.log("sonstiges");
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
    node.className = "clearfix right-align right card-panel green accent-1";
    node.innerHTML = query;
    resultDiv.appendChild(node);
  }

  function createResponseNode() {
    var node = document.createElement('div');
    node.className = "clearfix left-align left card-panel blue-text text-darken-2 hoverable";
    node.innerHTML = "...";
    resultDiv.appendChild(node);
    return node;
  }

  function setResponseOnNode(response, node) {
    node.innerHTML = response ? response : "[empty response]";
    node.setAttribute('data-actual-response', response);
    scrollToMessage();

    // var image = document.createElement('img');
    // image.src = "https://www.kleinwalsertal.com/website/var/tmp/image-thumbnails/160000/168628/thumb__portal-headerslide/traumskiwochen-1400x794.jpeg";
    // node.appendChild(image);
  }

  function hotelListOnNode(response,node) {
    node.innerHTML = "";
    $.each( response, function( key, value ) {
      var child = document.createElement('div');
      child.className = "attachment";
      var headline = document.createElement('h4');
      headline.innerHTML = value.title + " " + value.classification;
      child.append(headline);
      var image = document.createElement('img');
      image.src = value.image_url;
      image.height = 200;
      child.append(image);
      var text = document.createElement('p');
      text.innerHTML = value.type;
      child.append(text);
      var address = document.createElement('p');
      address.innerHTML = value.address;
      child.append(address);
      node.appendChild(child);
      console.log( value );
    });
    scrollToMessage();
  }


  function imageListOnNode(response, node) {
    node.innerHTML = "";
    node.className = "detail-slick clearfix card-panel hoverable detail-slick";
    $.each( response, function( key, value ) {
      if (key < 10) {
        var slick = document.createElement('div');
        slick.className = "detail-slick__item";
        var embed = document.createElement('div');
        embed.className = "embed-responsive embed-responsive-3by2";
        var image = document.createElement('img');
        image.title = value['title'];
        image.src = value['link'];
        image.height = 200;
        embed.append(image);
        slick.append(embed);
        node.append(slick);
      }
      // child.className = "attachment";
      // var headline = document.createElement('h4');
      // headline.innerHTML = value.title + " " + value.classification;
      // child.append(headline);
      // var image = document.createElement('img');
      // image.src = value.image_url;
      // image.height = 200;
      // child.append(image);
      // var text = document.createElement('p');
      // text.innerHTML = value.type;
      // child.append(text);
      // var address = document.createElement('p');
      // address.innerHTML = value.address;
      // child.append(address);
      // node.appendChild(child);
      // console.log( value );
    });

    // $.getScript('slick.min.js').done(function () {
    //   $('.detail-slick').slick({
    //     lazyLoad: 'progressive',
    //     mobileFirst: true,
    //     slidesToShow: 1,
    //     slidesToScroll: 1
    //   });
    // });
    // $('.slick-slider').
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
    if ($('#result-wrapper').height() > 800) {
      $('html, body').animate({
        scrollTop: $("#q").offset().top - 100
      }, 2000);
    }
  }

})();
