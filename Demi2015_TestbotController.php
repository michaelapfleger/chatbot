<?php
class Demi2015_TestbotController extends Demi2015_Website_Controller_Action
{

    public $params = null;

    public function init()
    {
        parent::init();

        Deskline_Init::init();
        $front = Zend_Controller_Front::getInstance();
        $front->unregisterPlugin("Pimcore_Controller_Plugin_Cache");
        //$this->view->headScript()->appendFile('/static/demi/js/typeahead.min.js', 'text/javascript');
        /*
        $this->view->headScript()->appendFile('/static/demi2015/js/history.min.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/jquery-ui-1.10.3.custom.min.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/jquery.slider.min.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/validate_bt3.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/demi-script.js?_dc=' . time(), 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/bootstrap-custom-init.js', 'text/javascript');
		*/

        $this->view->jsConfig('_config')->add('detailShowAllProducts', Demi2015_Website_Helper::detailShowAllProductsEnabled());

        $this->view->availableNightRanges = $this->availableNightRanges;

        $this->view->jsConfig('_config')->add('dateFormatShort', 'dd.mm.yy');
        $this->view->jsConfig('_config')->add('dateFormatLong', 'DD, d. MM yy');
        $this->view->zendDateFormatShort = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
        $this->view->zendDateFormatLong = Zend_Date::WEEKDAY.', '.Zend_Date::DAY_SHORT . ". " . Zend_Date::MONTH_NAME . " " . Zend_Date::YEAR;
    }

    /**
     * @param Demi_AccommodationServiceProvider $acco
     */
    public function checkValidUrl($acco,$housePackage) {
        if(!$acco || $this->isAffiliate) {
            return;
        }
        $realAccoUrl = Demi2015_Website_Helper::createUrlForAcco($acco, $_GET, $this->view);
        $noParams = explode("?", $realAccoUrl);


        if($housePackage){
            $realPackageUrl = Demi2015_Website_Helper::createUrlForPackage($acco,$housePackage, $_GET, $this->view);
            $packageNoParams = explode("?", $realPackageUrl);
            if($noParams[0] != rawurldecode($this->_request->getPathInfo()) && $packageNoParams[0] != rawurldecode($this->_request->getPathInfo())) {
                $this->redirect($realPackageUrl, array("code" => 301));
            }

        } else {
            if($noParams[0] != rawurldecode($this->_request->getPathInfo())) {
                $this->redirect($realAccoUrl, array("code" => 301));
            }
        }




    }

    public function searchAction()
    {
        $this->enableLayout();
        $this->view->fromSearchAction = true;
    }


    public function listAction()
    {
        //$this->view->headScript()->appendFile('/static/demi2015/js/elements-range-picker.js', 'text/javascript');
        //$this->view->jsConfig('_config')->add('demiElementsRangePicker', true);

        $this->enableLayout();

        if (!$this->editmode) {

            //$starsList = new Demi_List_Stars();
            $this->view->starsParamName = "stars[]";
            $this->view->classificationParamName = "classifications[]";


            //$themeList = new Demi_List_HolidayTheme();
            //$themeList->setLimit(10);
            $this->view->themeParamName = "holidaythemes[]";

            //$facilityList = new Demi_List_Facility();
            //$facilityList->setLimit(10);
            $this->view->facilityParamName = "facilities[]";
            $this->view->roomFacilityParamName = "roomFacilities[]";
            $this->view->townParamName = "towns[]";
            $this->view->regionParamName = "regions[]";

            $this->view->categoryParamName = "categories[]";
            $this->view->mealtypeParamName = "mealtypes[]";

            $this->view->bookonlyParamName = "bookonly";
            $this->view->fulltextParamName = "fulltext";


            $this->checkParams();
            $params = $this->getSearchParams();
            $isCorridor = $params->getIsCorridor();

            $this->view->gaCategory1stLevel = $this->trackingService->getListCategory1stLevel($params);
            $this->view->gaEventCategory = $this->trackingService->getListEventCategory($params);
            $this->view->jsConfig('_config')->add('demiEnhancedEcommerceCategory1stLevel', $this->view->gaCategory1stLevel);
            $this->view->jsConfig('_config')->add('demiEnhancedEcommerceEventCategory', $this->view->gaEventCategory);

            if($isCorridor) {
                //$this->getCorridorSearchList($params);
                p_r("forward to corridor list");die();
                $this->forward("corridor-list");
            } else {
                $time1= millitime();
                $this->getSearchList($params);
                if($_GET["gimmetime"] && Pimcore::inDebugMode()) {
                    p_r("TIME: " . (millitime() - $time1));
                }
            }
        }


        $this->view->jsConfig('_config')->add('showAdditionalSearchResults', Demi2015_Configuration_Helper::getDetailShowAdditionalSearchResults()?true:false);
        $this->view->jsConfig('_config')->add('additionalSearchIgnoreFilterFields', explode(',', Demi2015_Configuration_Helper::getAdditionalSearchIgnoreFilterFields()));
    }

    public function listGetAllSettlersAction()
    {
        $this->enableLayout();

        if (!$this->editmode) {

            //$starsList = new Demi_List_Stars();
            $this->view->starsParamName = "stars[]";
            $this->view->classificationParamName = "classifications[]";


            //$themeList = new Demi_List_HolidayTheme();
            //$themeList->setLimit(10);
            $this->view->themeParamName = "holidaythemes[]";

            //$facilityList = new Demi_List_Facility();
            //$facilityList->setLimit(10);
            $this->view->facilityParamName = "facilities[]";
            $this->view->townParamName = "towns[]";
            $this->view->regionParamName = "regions[]";

            $this->view->categoryParamName = "categories[]";
            $this->view->mealtypeParamName = "mealtypes[]";

            $this->view->bookonlyParamName = "bookonly";
            $this->view->fulltextParamName = "fulltext";


            $this->checkParams();
            $params = $this->getSearchParams();

            if($params->getIsCorridor()) {
                //$this->getCorridorSearchList($params);
                $this->forward("corridor-list");
            } else {
                $this->getSearchList($params);
            }
        }



        //$bubbleHelper = new Demi2015_Accommodation_Search_Service_BubbleCalculator($this->view->list->getResultSet(), $this->getDc());
        //$this->view->bubbles = $bubbleHelper->getCountForDestIds($this->getAllDesids());

    }

    public function mapAction()
    {
        /*
        $this->view->headScript()->appendFile('/static/demi2015/js/elements-range-picker.js', 'text/javascript');

        $this->view->headScript()->appendFile('/static/demi2015/js/marker-cluster.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/infobox.js', 'text/javascript');
		*/
        $this->enableLayout();

        if (!$this->editmode) {

            //$starsList = new Demi_List_Stars();
            $this->view->starsParamName = "stars[]";
            $this->view->starsList = $this->view->multihref("starsList");

            //$themeList = new Demi_List_HolidayTheme();
            //$themeList->setLimit(10);
            $this->view->themeParamName = "holidaythemes[]";
            $this->view->themeList = $this->view->multihref("themeList");

            //$facilityList = new Demi_List_Facility();
            //$facilityList->setLimit(10);
            $this->view->facilityParamName = "facilities[]";
            $this->view->facilityList = $this->view->multihref("facilityList");

            $this->checkParams();
            $params = $this->getSearchParams();

            $this->getSearchList($params);
        }

    }

    /**
     * @param $params Demi_Accommodation_Search_Parameter
     */
    public function getSearchList($params)
    {

        if($params->getIsCorridor()) {
            $list = $this->getCorridorSearchList($params);
            return $list;
        }

        $dc = $this->getDc();
        if($this->getParam("livesearch")) {
            $time1 = millitime();
//            $params->setRegions(array(425424));
            $params->setPerPage(200);
            $list = new Deskline_Accommodation_Search_List_VacancyLive($dc, $params, 1, 200);
            $list->load();
            //p_r(millitime() - $time1);die();
        } else {
            $list = new Demi_Accommodation_Search_List_VacancyLocal($dc, $params);
            if(! $_GET["nogearman"] && $params->getDateFrom() && $params->getDateTo() && false) {
                $list->setAdapter(new Demi_Accommodation_Search_Service_Vacancies_Local_Gearman($dc, $params));
            }
        }

        $page = 1;
        if ($this->getParam("page")) {
            $page = $this->getParam("page");
        }


        if($params->getSearchType() == Demi_Accommodation_Search_Parameter::SEARCH_TYPE_EACH_PRODUCT_SEPARATED) {
            $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PRODUCTS);
        } else if ($params->getSearchType() == Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE) {
            $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PACKAGES);
        }

        $pagesize = 10;

        $paginator = Zend_Paginator::factory($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($pagesize);

        //$this->view->filterDefinitionObject = $filterDefinition;
        //$this->view->filterService = $filterService;
        $this->view->list = $list;
        $this->view->searchParams = $params;
        $this->view->dc = $dc;
        $this->view->paginator = $paginator;

        return $list;

    }

    public function ajaxGetResultAction()
    {
        header("Content-Type: application/json");
        $this->checkParams();
        $params = $this->getSearchParams();
        $this->view->dc = $this->getDc();
        //$changedParameter = $this->getParam("changedParam");
        $this->getSearchList($params);
        /*if($params->getSearchType() == Demi_Accommodation_Search_Parameter::SEARCH_TYPE_EACH_PRODUCT_SEPARATED) {
            $this->disableViewAutoRender();
            $this->renderScript("/demi2015/search-accommodation/ajax-get-result-packages.php");
        }*/
    }

    public function ajaxGetMapDetailAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();
        $accoId = (int)$this->getParam("accoId");

        $dc = $this->getDc();

        //TODO chenge to parameter datefrom
        $dateFrom = new Zend_Date();

        $acco = Demi_AccommodationServiceProvider::getById($accoId);
        if (!$acco) {
            echo "";
            die();
        }

        $imgUrl = "";
        if (($listimage = $acco->getFirstImage(null, $dateFrom)) instanceof Asset_Image) {
            $imgUrl = $listimage->getThumbnail("demi_responsive_list")->getPath();
        }

        $name = $acco->getName() . Demi2015_Website_Helper::desklineStars($acco);

        $address = $acco->getAddress();
        $addressInfos = $address->getAddressLine1() . " " . $address->getAddressLine2() . ", " . $address->getZipcode() . " " . $address->getTown() . " " . $address->getCity() . ", " . $address->getCountry();

        $ratingAverageMethod = "get" . ucfirst($dc->getRatingAverageColumn());
        $ratingCountMethod = "get" . ucfirst($dc->getRatingCountColumn());

        $addInfo = array(
            "headline"    => $name,
            "subheadline" => $addressInfos,
            "imgURL"      => $imgUrl,
            "rating"      => $acco->$ratingAverageMethod(),
            "ratingSum"   => $acco->$ratingCountMethod(),
        );

        echo json_encode($addInfo);
        die();
    }

    public function ajaxGetMapResultAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();
        $this->checkParams();
        $params = $this->getSearchParams();
        $params->setExtendedResultset(true);
        $this->getSearchList($params);

        $list = $this->view->list;

        $jsonArray = array();

        /** @var $accoResultSet Demi_Accommodation_Search_ResultSet_Accommodation */
        $limit = 10;
        $count = 0;

        $format = Zend_Locale_Data::getContent($this->curr->getLocale(), 'currencynumber');


        $accosIterated = array();


        foreach ($list->getItems(null,null) as $accoResultSet) {
            if($accosIterated[$accoResultSet->getAccommodationId()]){
                //check if previous has lower price - if yes skip the second offer with higher price (would overlap on map)
                if($accoResultSet->getMinPrice()>$accosIterated[$accoResultSet->getAccommodationId()]->getMinPrice()){
                    continue;
                }
            }
            $addressInfos = $accoResultSet->getAddressLine1() . " " . $accoResultSet->getAddressLine2() . ", " . $accoResultSet->getZipcode() . " " . $accoResultSet->getTown() . " " . $accoResultSet->getCity() . ", " . $accoResultSet->getCountry();
            $tmpArray = array();
            //$acco = Demi_AccommodationServiceProvider::getById($accoResultSet->getAccommodationId());
            if ($accoResultSet->getLatitude() && $accoResultSet->getLongitude()) {
                if (!$accoResultSet->getRatingSum() || !$accoResultSet->getRating() || $accoResultSet->getRating() <= 0) {
                    $rating = -1;
                } else {
                    $rating = $accoResultSet->getRating();
                }

                if (empty($addressInfos)) {
                    $addressInfos = null;
                }

                $stars = "";
                for ($s = 0; $s < $accoResultSet->getStarNr(); $s++) {
                    $stars .= "*";
                }

                if ($accoResultSet->getStarSup()) {
                    $stars .= "s";
                }

                $detailUrl = Demi2015_Website_Helper::createUrlForAcco(
                    null, array_merge($_GET, array("mp" => $accoResultSet->getMinPrice())), $this->view, $this->language, utf8_encode($accoResultSet->getName()), $accoResultSet->getAccommodationId()
                );

                $tmpArray["acco_id"] = $accoResultSet->getAccommodationId();
                $tmpArray["lat"] = $accoResultSet->getLatitude();
                $tmpArray["lng"] = $accoResultSet->getLongitude();
                $tmpArray["imgUrl"] = "ajax?ajaxRequestType=getMapDetailImage&accoId=" . $accoResultSet->getAccommodationId();
                $tmpArray["rating"] = $rating;
                $tmpArray["fromRating"] = "100";
                $tmpArray["ratingSum"] = $accoResultSet->getRatingSum() . " " . $this->view->translate("Bewertungen");
                $tmpArray["headline"] = utf8_encode($accoResultSet->getName() . " " . $stars);
                $tmpArray["subheadline"] = utf8_encode($addressInfos);
                $tmpArray["price"] = $this->curr->toCurrency($accoResultSet->getMinPrice(), array("precision" => 2, "format"=>$format));
                $tmpArray["pricePostFix"] = "";
                if($params->getIsCorridor()) {
                    $tmpArray["pricePostFix"] = $this->view->translate("demi.PerPerson") . " / " . $this->view->translate("demi.Nacht");
                }
                $tmpArray["from"] = $this->view->translate("demiAb");
                $tmpArray["detailUrl"] = str_replace("//", "/", $detailUrl);
                $jsonArray[] = $tmpArray;
                $accosIterated[$accoResultSet->getAccommodationId()]=$accoResultSet;
            }
            $count++;
        }

        $jsonString = json_encode($jsonArray);

        echo $jsonString;
        exit();

    }

    public function checkParams()
    {
        //check if all needed Params are available
        if ($this->getParam("a0") <= 0 || $this->getParam("c0") < 0 || !$this->getParam("from") || !$this->getParam("to")) {
            //throw new Exception("Missing Paramaters");
            return false;
        }
        return true;
    }

    public function detailAction()
    {

        // corridor matrix
        /*
        $this->view->headScript()->appendFile('/static/demi2015/js/owl.carousel.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/corridor-matrix.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/search-bar.js', 'text/javascript');
        $this->view->headScript()->appendFile('/static/demi2015/js/jquery-scrollto.js', 'text/javascript');
        */
        $this->view->jsConfig('_config')->add('demiOwlCarousel', true);
        $this->view->jsConfig('_config')->add('demiCorridorMatrix', true);
        $this->view->jsConfig('_config')->add('demiScrollTo', true);
        $this->view->jsConfig('_config')->add('stickyElements', true);

        $this->view->availableNightRanges = $this->availableNightRanges;
        $this->enableLayout();

        if ($this->getParam("enquire") && Demi2015_Configuration_Helper::getDisableEnquiry() != true) {
            $this->detailEnquireAction();
        }

        if($this->getParam("minFromDate")) {
            $this->isCorridorSearch = true;
            $this->view->isCorridorSearch = true;
        }

        $accoId = $this->getParam("accoId");

        $this->view->baseSearchUrl = Demi2015_Website_Helper::getBaseSearchUrl($this->language) . "/" . Demi2015_Website_Helper::getUrlWithNewParam($_GET, array());

        /** @var Demi_AccommodationServiceProvider $acco */
        $acco = Demi_AccommodationServiceProvider::getById($accoId);
        if (!($acco instanceof Demi_AccommodationServiceProvider)
            || ($acco->getIsTesthotel() && !$this->getParam("includeTest") && !Pimcore::inDebugMode()) || !$acco->getPublished()) {
            throw new Zend_Controller_Router_Exception("No Accommodation found");
        }

        $packageId = $this->getParam("housePackage");
        if ($packageId){
            $package = Demi_HousePackageMaster::getById($packageId);
            if (!($package instanceof Demi_HousePackageMaster) || !$package->isPublished()) {
                // set package to null in case of invalid or unpublished packages => checkValidUrl will redirect to hotel detail page
                $package = null;
            }
            $this->view->housePackage = $package;
        }

        $this->checkValidUrl($acco,$package);


        $clickCountService = new Deskline_DSI_Service_ClickCount();
        $this->view->jsConfig('_config')->add('countingClicks', true);
        $this->view->jsConfig('_config')->add('countingClicksUriJs', str_replace('http://', 'https://', $clickCountService->getClickCountUrl($acco)));

        $this->setHasMail($acco);

        $params = $this->getSearchParams();
        //override bookable
        $params->setBookOnly(false);
        $dc = $this->getDc();
        $this->view->dc = $dc;
        $params->setOrder(array());
        $params->setOrderKey(array());
        $params->setRefilterCustomOrder("");
        $params->setOrderRandSeed(null);
        $this->view->noArrival = false;
        if (!$this->checkParams() || $this->getParam("noArrival") == "on") {
            $this->view->noArrival = true;
            $nights = $this->getNightsArray();
            $params->setNights($nights[0]);

        }

        if(!$package){
            //calculate best offer for all house package masters of this acco
            $this->preparePackageMasters($acco->getId());
        } else {

            $validDates = $package->getValidDates();
            $validArray=array();
            if($validDates){
                foreach($validDates->getItems() as $item ) {
                    if($item->getTo()->isLater(new Zend_Date())) {
                        $validArray[] = array("start" => $item->getFrom()->getTimestamp(), "end" => $item->getTo()->getTimestamp());
                    }
                }
            }

            //add validity dates for datepicker
            $this->view->jsConfig('_config')->add('datepickerRanges', $validArray);

            //calculate the best offer for current house package
            if($params->getDateFrom() and $params->getDateTo()){
                $params->setSearchType(Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE);
                $adapter=null;
                if($params->getIsCorridor()){
                    $adapter = new Demi_Accommodation_Search_Service_Vacancies_Corridor($this->getDc(), $params);
                }
                $list = new Demi_Accommodation_Search_List_VacancyLocal($this->getDc(), $params, $adapter);
                $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PACKAGES);
                $result = $list->getResultSet();
                $this->view->housePackageResult = $result->getHousePackages(0,null,true,$package->getId());

            } else {
                //calculate cheapest price for this house package
                $productIds = array();
                foreach($package->getProducts() as $p){
                    $productIds[]=$p->getId();
                }
                $params = new Demi_Accommodation_Search_Parameter();
                $params->setProductIds($productIds);
                $params->setAccoIds(array($accoId));
                $params->setCalculateStandardPrice(true);
                $params->setUseMultiLineMerge(true);
                $params->setOrderKey(array("bookable","Random"));
                $params->setRegions(array());
                $params->setTowns(array());
                $params->setMealTypeId(array());
                $params->setRoomrows(array());
                $params->setProductType("Package");
                $params->setOrderRandSeed("1");
                $params->setSearchType(Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE);
                $list = new Demi_Accommodation_Search_List_VacancyLocal($this->getDc(), $params);
                $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PACKAGES);
                $result = $list->getResultSet();
                $this->view->housePackageResult = $result->getHousePackages(0,null,true,$package->getId());;
            }
        }


        $this->view->searchParams = $params;
        $this->view->acco = $acco;
        $this->view->housePackageId=$this->getParam("housePackage");

        if($this->getParam("minDate")) {
            $minDate = DateTime::createFromFormat("d.m.Y", $this->getParam("minDate"));
        }




        //get local resultSet but without the product restriction to find out all possible Products:
        $corrParams = clone $params;
        $corrParams->setProductIds(array());
        $localadapter = new Demi_Accommodation_Search_Service_Vacancies_Corridor($this->getDc(), $corrParams);

        if($package){
            if($this->view->housePackageResult and count($this->view->housePackageResult)>0){
                $localResultSet = $this->view->housePackageResult[0];
            }
        } else {
            $localResultSet = Demi_Accommodation_Search_ResultSet_Accommodation::getById($accoId, $localadapter);
        }
        $this->view->localResultSet = $localResultSet;
        if(!$minDate && $params->getIsCorridor()) {
            if($localResultSet) {
                $minDate = $localResultSet->getMinDateFrom($params->getDateFrom());
            }
        }
        $this->view->nothingFound = false;
        if(!$minDate || $minDate < $params->getDateFrom()) {
            if(!$params->getDateFrom()) {
                $minDate = new DateTime();
                $minDate->setTime(0, 0, 0);
            } else {
                $minDate = clone $params->getDateFrom();
            }
        }
        if($params->getDateFrom() && $params->getDateTo()) {
            if($minDate > $params->getDateTo() || $minDate < $params->getDateFrom()) {
                $this->view->nothingFound = false;
            }
        }


        $minDate = $this->getMondayFromWeekWithDate($minDate);

        $time1 = millitime();

        $validPeriods = $this->getNightsArray();
        $corridorMatrixSet = $this->getCorridorMatrix($acco->getId(), $minDate, 7, $validPeriods);
        $this->view->baseDateFrom = $corridorMatrixSet["baseDateFrom"];
        $this->view->possibleNights = $corridorMatrixSet["possibleNights"];
        $this->view->corridorMatrix = $corridorMatrixSet["corridorMatrix"];

        $cheapestWeek = $this->getCheapestPriceFromCorridorMatrix($corridorMatrixSet["corridorMatrix"], $corridorMatrixSet["possibleNights"]);
        $this->view->cheapestWeek = $cheapestWeek;
        $time2 = millitime() - $time1;

        // Ecommerce tracking
        $gaProductObject = $this->trackingService->getGoogleAnalyticsProductObject($acco, $params);
        $this->view->jsConfig('_config')->add('demiEnhancedEcommerceDetailProduct', $gaProductObject);



        // Redirected from cart because the deskline cart could not be created
        $env = OnlineShop_Framework_Factory::getInstance()->getEnvironment();
        if($env->getCustomItem('demi_deskline_cart_creation_failed')) {
            $this->view->redirectedAfterDesklineException = true;
            $env->removeCustomItem('demi_deskline_cart_creation_failed');
            $env->save();
        }

    }

    public function getMondayFromWeekWithDate($minDate) {
        $dayOfWeek = (int) $minDate->format("w");
        if($dayOfWeek != 1) {
            $sub = $dayOfWeek - 1;
            if($sub <= 0) {
                $sub = 6;
            }
            $minDate->sub(new DateInterval('P' . $sub . 'D'));
        }
        return $minDate;
    }

    public function getCheapestPriceFromCorridorMatrix($matrix, $nightsArr) {
        $cheapestArr = array();
        foreach($nightsArr as $nights) {
            $cheapestObj = new stdClass();
            foreach($matrix as $priceArr) {
                $nightPriceToUse = $priceArr["nights"][$nights];
                if($cheapestObj->price) {
                    $cheapestPrice = $cheapestObj->price;
                }
                if(((!$cheapestObj->date) || $nightPriceToUse < $cheapestPrice) && $nightPriceToUse > 0) {
                    $cheapestObj = new stdClass();
                    $cheapestObj->date = $priceArr["date"];
                    $cheapestObj->nights = $nights;
                    $cheapestObj->price = $nightPriceToUse;
                }
            }
            $cheapestArr[$nights] = $cheapestObj;
        }

        return $cheapestArr;
    }

    public function setHasMail($acco) {
        if(! $acco instanceof Demi_AccommodationServiceProvider && is_numeric($acco)) {
            $acco = Demi_AccommodationServiceProvider::getById($acco);
        }
        if($acco instanceof Demi_AccommodationServiceProvider && ($addy = $acco->getAddress())) {
            $email = $addy->getEmail();
            $this->accoHasEmail = $email && !empty($email);
            $this->view->accoHasEmail = $this->accoHasEmail;
        }
    }

    public function ajaxGetLivePaymentInformationAction() {
        $this->disableLayout();
        $product = Demi_AccommodationProduct::getById($this->getParam("productId"));
        $roomRowIndex = $this->getParam("roomindex");
        $this->view->bookOnRequest = $this->getParam("bookonrequest") == "1" ? true : false;

        $acco = $product->getParent()->getParent();
        $params = $this->getSearchParams();
        $roomRows = $params->getRoomrows();
        $roomRow = $roomRows[$roomRowIndex];

        $service = new Deskline_Service_Search_PaymentInformation();
        $result = $service->getPaymentInformation($acco, $product, $acco->getDbcode(), null, $params->getDateFrom(), $params->getDateTo(), $roomRow->getAdults(), $roomRow->getChildAges());
        $this->view->paymentInfo = $result;

    }
    public function ajaxGetLiveCancellationInformationAction(){
        $this->disableLayout();
        /** @var Demi_AccommodationProduct $product */
        $product = Demi_AccommodationProduct::getById($this->getParam("productId"));


        $roomRowIndex = $this->getParam("roomindex");
        $params = $this->getSearchParams();
        if ($roomRowIndex!==null){
            $roomRows = $params->getRoomrows();
            $roomRow = $roomRows[$roomRowIndex];
            $adults = $roomRow->getAdults();
            $childAges = $roomRow->getChildAges();
        }else {
            $adults = $this->getParam("adults");
            $childAges = $this->getParam("childAges");
        }

        $acco = $product->getService()->getServiceProvider();


        $service = new Deskline_Service_Search_CancellationInformation();
        $result = $service->getCancellationInformation($acco, $product, $acco->getDbcode(), null, $params->getDateFrom(), $params->getDateTo(), $adults, $childAges);
        $this->view->dateFrom = $params->getDateFrom();
        $this->view->cancellationInformation = $result;
    }

    public function ajaxManagerAction()
    {
        $type = $this->getParam("ajaxRequestType");

        if ($type == "getLiveProducts") {
            $this->forward("ajax-get-live-products", "demi2015_search-accommodation");
        } else if ($type == "getLocalProducts") {
            $this->forward("ajax-get-local-products", "demi2015_search-accommodation");
        } else if ($type == "getLocalCorridorProducts") {
            $this->forward("ajax-get-local-corridor-products", "demi2015_search-accommodation");
        } else if ($type == "getLiveDetailProducts") {
            $this->forward("ajax-get-live-detail-products", "demi2015_search-accommodation");
        } else if ($type == "getDetailAdditionalProducts") {
            $this->forward("ajax-get-detail-additional-products", "demi2015_search-accommodation");
        } else if ($type == "getPaymentInformation") {
            $this->forward("ajax-get-live-payment-information", "demi2015_search-accommodation");
        } else if ($type == "getCancellationInformation") {
            $this->forward("ajax-get-live-cancellation-information", "demi2015_search-accommodation");
        } else if ($type == "getVacancyJson") {
            $this->forward("ajax-get-vacancy-json", "demi2015_search-accommodation");
        } else if ($type == "getNewResult") {
            $this->forward("ajax-get-result", "demi2015_search-accommodation");
        } else if ($type == "getNewMapResult") {
            $this->forward("ajax-get-map-result", "demi2015_search-accommodation");
        } else if ($type == "getMapAddDetails") {
            $this->forward("ajax-get-map-detail", "demi2015_search-accommodation");
        } else if ($type == "getMapDetailImage") {
            $this->forward("ajax-get-map-detail-image", "demi2015_search-accommodation");
        } else if ($type == "getBubbles") {
            $this->forward("ajax-get-bubbles", "demi2015_search-accommodation");
        } else if ($type == "getProductByIdAndMealcode") {
            $this->forward("ajax-get-product-by-id-and-mealcode", "demi2015_search-accommodation");
        } else if ($type == "getCorridorMatrix") {
            $this->forward("ajax-get-corridor-matrix", "demi2015_search-accommodation");
        } else if ($type == "getNewCorridorMatrix") {
            $this->forward("ajax-get-new-corridor-matrix", "demi2015_search-accommodation");
        } else if ($type == "getCheapestForNights") {
            $this->forward("ajax-get-cheapest-for-nights", "demi2015_search-accommodation");
        }
    }

    public function ajaxGetMapDetailImageAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        $accoId = (int)$this->getParam("accoId");
        $dateFrom = new Zend_Date($this->getParam("dateFrom"));
        $acco = Demi_AccommodationServiceProvider::getById($accoId);
        $imgThumb = null;
        $mimeType = 'image/jpeg';
        if (($firstImg = $acco->getFirstImage(null, $dateFrom)) instanceof Asset_Image) {
            /** @var $firstImg Asset_Image */
            $imgThumb = $firstImg->getThumbnail("demi_responsive_list");
            $imgThumb->generate();
            $mimeType = $firstImg->getMimetype();
        } else {

        }

        header('Content-type: ' . $mimeType);
        if($imgThumb) {
            readfile(PIMCORE_DOCUMENT_ROOT . $imgThumb->getPath());
        }
        exit();
        //$this->view->imageData = file_get_contents(PIMCORE_DOCUMENT_ROOT . $imgThumb->getPath());
    }


    public function prepareDetailAcco($params = null)
    {
        if (!$this->checkParams()) {
            die();
        }
        if($params === null) {
            $params = $this->getSearchParams();
            $params->setClassifications(array());
            $params->setStars(array());
        }
        //$params->setLastAvailabilitiyChangeDays(null);
        $dc = $this->getDc();
        //$dc->setIgnoreCache(false);
        //ovverride bookable  for detailpage
        $params->setBookOnly(false);
        $params->setOrder(array());
        $params->setOrderKey(array());
        $params->setOrderRandSeed(null);
        $params->setSearchType(Demi_Accommodation_Search_Parameter::SEARCH_TYPE_STANDARD); //override searchtype if producttype = package

        $accoId = $this->getParam('accoId');

        $liveadapter = new Deskline_Accommodation_Search_Service_Vacancies_Live($dc, $params);
        $liveResultSet = Demi_Accommodation_Search_ResultSet_Accommodation::getById($accoId, $liveadapter);

        //split result into packages and rooms


        $this->setHasMail($accoId);

        $this->view->resultSet = $liveResultSet;
        $this->view->searchParams = $params;
        $this->view->minpriceIds = explode(";", $this->getParam("minpriceids"));
        $this->view->minpricePrices = explode(";", $this->getParam("minpriceprices"));

    }


    public function preparePackageMasters($accoId){

        /** @var Demi_AccommodationServiceProvider $accommodation */
        $accommodation = Demi_AccommodationServiceProvider::getById($accoId);
        $allHousePackageMasters = array();
        $housePackageMasters = $accommodation->getHousePackageMasters();
        $products = array();
        foreach($housePackageMasters as $housePackageMaster) {
            $validDates = $housePackageMaster->getValidDates();
            $hasValidDates = false;
            if ($validDates instanceof Pimcore\Model\Object\Fieldcollection) {
                $dates = $validDates->getItems();
                if (is_array($dates) and count($dates) > 0) {
                    foreach ($dates as $item) {
                        //only show packages with at least one current or future valid dates
                        if ($item->getTo()->isLater(new Zend_Date())) {
                            $hasValidDate = true;
                            break;
                        }
                    }
                    if ($hasValidDate) {
                        $products = array_merge($products, $housePackageMaster->getProducts());
                    }
                }
            }
        }


        if(count($products)>0){
            $productIds = array();
            foreach($products as $p){
                $productIds[]=$p->getId();
            }
            $params = new Demi_Accommodation_Search_Parameter();
            $params->setProductIds($productIds);
            $params->setAccoIds(array($accoId));
            $params->setCalculateStandardPrice(true);
            $params->setUseMultiLineMerge(true);
            $params->setOrderKey(array("bookable","Random"));
            $params->setRegions(array());
            $params->setTowns(array());
            $params->setMealTypeId(array());
            $params->setRoomrows(array());
            $params->setProductType("Package");
            $params->setOrderRandSeed("1");
            $params->setSearchType(Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE);
            $list = new Demi_Accommodation_Search_List_VacancyLocal($this->getDc(), $params);
            $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PACKAGES);
            $result = $list->getResultSet();
            $packages=$result->getHousePackages($housePackageMaster->getId());
        }
        $this->view->packages = $packages;

    }

    public function prepareAdditionalRooms ($accoId, array $excludedProductIds=[]){
        /** @var Demi_AccommodationServiceProvider $accommodation */
        $accommodation = Demi_AccommodationServiceProvider::getById($accoId);
        $this->setHasMail($accommodation);
        /** @var Demi_AccommodationProduct[] $allProducts */
        $allProducts = $accommodation->getAccommodations(false);
        foreach ( $allProducts as $product )
        {
            $productId = $product->getId();
            if(!in_array($productId, $excludedProductIds)) {
                $allRoomsArray[$productId] = $product;
            }
        }

        if($accommodation instanceof Demi_AccommodationServiceProvider && empty($this->view->acco))
            $this->view->acco = $accommodation;
        $from = $this->getParam('from');
        $this->view->noDate = empty($from);
        $this->view->additionalRooms = $allRoomsArray;
    }

    public function prepareLocalDetailAcco($params = null, $accoId)
    {
        if($params === null) {
            $params = $this->getSearchParams();
        }

        $dc = $this->getDc();
        //$dc->setIgnoreCache(false);

        $params->setOrder(array());
        $params->setOrderKey(array());
        $params->setOrderRandSeed(null);
        $params->setRefilterCustomOrder("");
        $params->setSearchType(Demi_Accommodation_Search_Parameter::SEARCH_TYPE_STANDARD); //override searchtype if producttype = package

        $localadapter = new Demi_Accommodation_Search_Service_Vacancies_Local($dc, $params);

        //$accoId = $this->getParam("accoId");


        $localResultSet = Demi_Accommodation_Search_ResultSet_Accommodation::getById($accoId, $localadapter);

        return $localResultSet;

//        $this->view->resultSet = $liveResultSet;
//        $this->view->searchParams = $params;
//        $this->view->minpriceIds = explode(";", $this->getParam("minpriceids"));
//        $this->view->minpricePrices = explode(";", $this->getParam("minpriceprices"));
    }

    public function ajaxGetLiveProductsAction()
    {
        $this->disableLayout();
        $dc = $this->getDc();
        $this->view->dc = $dc;
        $this->view->searchParams = $this->getSearchParams();
        try {
            $this->prepareDetailAcco();
        } catch(Exception $e) {

        }
    }

    public function ajaxGetLocalProductsAction()
    {
        $this->disableLayout();
        $dc = $this->getDc();

        $accoId = $this->getParam("accoId");
        $acco = Demi_AccommodationServiceProvider::getById($accoId);
        $this->view->acco = $acco;
        $this->view->dc = $dc;

        $resultSet = unserialize($this->getParam("resultSet"));

        $this->view->resultSet = $resultSet;
    }

    public function ajaxGetLiveDetailProductsAction()
    {
        try {
            $this->disableViewAutoRender();
            $accoId = $this->getParam("accoId");
            $acco = Demi_AccommodationServiceProvider::getById($accoId);
            $dc = $this->getDc();
            $this->view->dc = $dc;
            $this->view->acco = $acco;

            try {
                $this->prepareDetailAcco();
            } catch (Exception $e) {
                $this->view->searchParams = $this->getSearchParams();
                throw  $e;
            }
            $jsonArray = array();
            /** @var Demi_Accommodation_Search_ResultSet_Accommodation $resultSet */
            $resultSet = $this->view->resultSet;
            if (!$resultSet) {
                $time = millitime();
                header("Content-Type: application/json");
                $jsonArray["status"] = "failed";
                //            if($this->getParam('mobile') == 1){
                //                $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-live-products-mobile.php");
                //            } else {
                $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-live-detail-products.php");
                //            }
                echo json_encode($jsonArray);
                exit();
            }
            $jsonArray["status"] = "success";
            $jsonArray["minprice"] = "<span class='owl-btn__from'>" . $this->view->translate("demi.Ab") . "</span><span class=\"btn-block strong\">" . $this->curr->toCurrency($resultSet->getMinPrice(), array("precision" => 2)) . "</span>";
            //        if($this->getParam('mobile') == 1){
            //            $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-live-products-mobile.php");
            //        } else {
            $jsonArray["housePackage"]=0;
            if($this->getParam("housePackage")){
                $jsonArray["housePackage"]=$this->getParam("housePackage");
                $this->view->singlePackageRequest = true;
            }
            $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-live-detail-products.php");
            //        }
        } catch (Exception $e) {
            $jsonArray["status"] = "failed";
            Logger::err("exception while getting live products  " . $e->getMessage(),$e);
            $this->view->errorMsg = $this->view->translate("demi.error.detail.products-not-loaded");
            $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-live-detail-products.php");
        }

        header("Content-Type: application/json");
        echo json_encode($jsonArray);
        exit();
    }




    public function ajaxGetDetailAdditionalProductsAction() {
        $this->disableViewAutoRender();

        $accoId = $this->getParam('accoId');
        $excludeIds = $this->getParam('excludeIds')?:[];
        if(!is_array($excludeIds) && is_numeric($excludeIds)) {
            $excludeIds = [$excludeIds];
        }

        $jsonArray = ["status"=>"failed","html"=>""];
        if($accoId && is_array($excludeIds))
        {
            $this->view->isEmptyResult = empty($excludeIds);
            $this->view->searchParams = $this->getSearchParams();
            $this->prepareAdditionalRooms($accoId, $excludeIds);
            $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-live-products-additional.php");
            $jsonArray["status"] = "success";
        }

        header("Content-Type: application/json");
        echo json_encode($jsonArray);
        exit();
    }

    public function ajaxGetProductByIdAndMealcodeAction()
    {
        $this->disableViewAutoRender();

        $productId = $this->getParam("productId");
        $mealcode = $this->getParam("mealcode");
        $settlercode = $this->getParam("settlercode");
        $roomindex = $this->getParam("roomindex");
        $params = $this->getSearchParams($roomindex);
        $params->setClassifications(array());
        $params->setStars(array());

        $p = Demi_AccommodationProduct::getById($this->getParam("productId"));
        if($p && $p->getParent()) {
            $acco = $p->getParent()->getParent();
        }

        if($acco instanceof Demi_AccommodationServiceProvider) {
            Deskline_Config::setClientKeyOverrideFromObject($acco);
        }


        /** @var Demi_Accommodation_Search_ResultSet_Product $productSet */
        $productSet = Demi_Accommodation_Search_ResultSet_Product::getByIdAndMealcode(
            $productId, $mealcode, new Deskline_Accommodation_Search_Service_Vacancies_Live($this->getDc(), $params)
        );

        if ($productSet) {
            $this->view->productSet = $productSet;
            $roomRows = $params->getRoomrows();
            $this->view->roomRow = $roomRows[0];

            $jsonArray = json_encode(
                array(
                    "status"      => "success",
                    "price"       => $productSet->getPrice(),
                    "productData" => Demi2015_Website_Helper::getProductBookingInfo($productSet, $roomindex, $settlercode),
                    "priceDetailHtml" => $this->view->render("/demi2015/search-accommodation/includes/price-info.php")
                )
            );
        } else {
            $jsonArray = json_encode(array("status" => "failed"));
        }

        echo $jsonArray;
        exit();

    }

    public function ajaxGetVacancyJsonAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();
        $params = $this->getSearchParams();

        $months = 12;

        $config = Deskline_Config::getInstance();

        $calendarService = new Deskline_DSI_Service_BasicData_AvailabilityCalendar($config);
        $calendarService->setCachetime(900);
        $accoId = $params->getAccoIds();
        if ($accoId) {
            if (is_array($accoId)) {
                $accoId = $accoId[0];
            }
        } else {
            throw new Exception("No AccoId given or not found");
        }
        $acco = Demi_AccommodationServiceProvider::getById($accoId);
        $today = new DateTime();
        $calendar = $calendarService->getAvailabilityCalendar($today, $months, $acco);

        $validDates = array();
        $onDates = array();
        $offDates = array();

        /** @var $calendarProduct Demi_AvailabilityCalendar_Calendar */
        foreach ($calendar as $productId => $calendarProduct) {
            /** @var $demiCal Demi_AvailabilityCalendar_Day */
            foreach ($calendarProduct->getDays() as $dayStr => $demiDay) {
                if ($demiDay->getCount() > 0) {
                    $doy = (String)$demiDay->getDayOfYear();
                    if (strlen($doy) < 3) {
                        $tmpZeros = 3 - strlen($doy);
                        for ($k = 0; $k < $tmpZeros; $k++) {
                            $doy = "0" . $doy;
                        }
                    }

                    $day = $demiDay->getDate()->format("Y") . $doy;

                    $validDates[$day] = true;
                    if ($demiDay->getArrivalPossible()) {
                        $onDates[$day] = true;
                    }
                    if ($demiDay->getArrivalPossible() && $demiDay->getDeparturePossible()) {
                        $offDates[$day] = true;
                    }
                    /* if($demiDay->getArrivalPossible() && $demiDay->getDeparturePossible()) {
                        if($offDates[$day] || $offDates[$day]) {
                            unset($offDates[$day]);
                            unset($onDates[$day]);
                        }
                        $validDates[$day] = true;
                    } else if($demiDay->getDeparturePossible()) {
                        if($validDates[$day]) {
                            unset($onDates[$day]);
                            unset($offDates[$day]);
                        } else if($offDates[$day]) {
                            unset($onDates[$day]);
                            unset($offDates[$day]);
                            $validDates[$day] = true;
                        } else {
                            $onDates[$day] = true;
                        }
                    } else if($demiDay->getArrivalPossible()) {
                        if($validDates[$day]) {
                            unset($onDates[$day]);
                            unset($offDates[$day]);
                        } else if($onDates[$day]) {
                            unset($onDates[$day]);
                            unset($offDates[$day]);
                            $validDates[$day] = true;
                        } else {
                            $offDates[$day] = true;
                        }
                    } */
                }
            }
        }

        /*
        $validDates["2013361"] = true;
        $validDates["2013363"] = true;
        $validDates["2013364"] = true;
        $validDates["2013365"] = true;
        $validDates["2014001"] = true;
        $validDates["2014002"] = true;
        $validDates["2014003"] = true;
        */

        echo json_encode(
            array(
                "validDates" => array_keys($validDates),
                "onDates"    => array_keys($onDates),
                "offDates"   => array_keys($offDates)
            )
        );
        die();
    }

    public function navigationAction()
    {
        $rootNode = Document::getByPath("/" . $this->_getParam("lang") . "/");
        $this->view->rootNode = $rootNode;
        $this->view->lang = $this->_getParam("lang");
        $this->view->doc = $this->_getParam("doc");
    }

    public function getFilterSnippetElements(){
        /** @var Document_Snippet $filterSnippetDoc */
        $filterSnippetDoc = Demi2015_Website_Helper::getListUrl($this->language, $this->document);

        if($filterSnippetDoc) {
            $filterSnippet = $filterSnippetDoc->getElement("filterSnippet");
        } else {
            return array();
        }
        if(!$filterSnippet) {
            return array();
        }

        /** @var \Pimcore\Model\Document\Snippet $tmpFilterSnippet */
        $tmpFilterSnippet = $filterSnippet->getElement();

        $possibleFilterSnippetElementNames = array();
        foreach($tmpFilterSnippet->getElements() as $el){
            $possibleFilterSnippetElementNames[]=$el->getName();
        }

        $safetyFirst = 0;
        while($tmpFilterSnippet->getContentMasterDocument() !== NULL ) {
            if($safetyFirst > 10) {
                break;
            }
            $tmpFilterSnippet = $tmpFilterSnippet->getContentMasterDocument();
            foreach($tmpFilterSnippet->getElements() as $currEl ){
                $possibleFilterSnippetElementNames[]=$currEl->getName();
            }

            $safetyFirst++;
        }

        $possibleFilterSnippetElementNames = array_unique($possibleFilterSnippetElementNames);
        $elements = array();
        $lastFilterSnippet = $filterSnippet->getElement();
        foreach($possibleFilterSnippetElementNames as $elementName){
            $e= $lastFilterSnippet->getElement($elementName);
            if($e){
                $elements[]= $e;
            }
        }
        return $elements;

    }



    // For new and sortable filter snippet
    public function getAllDesids($specificParams)
    {
        /** @var Document_Snippet $filterSnippetDoc */
        $filterSnippetDoc = Demi2015_Website_Helper::getListUrl($this->language, $this->document);
        if($filterSnippetDoc) {
            $filterSnippet = $filterSnippetDoc->getElement("filterSnippet");
        } else {
            return array();
        }
        if(!$filterSnippet) {
            return array();
        }
        $allArray = array();

        /** @var \Pimcore\Model\Document\Snippet $tmpFilterSnippet */
        $tmpFilterSnippet = $filterSnippet->getElement();


        $elements = $filterSnippet->getElement()->getElements();
        $savetyFirst = 0;

        while($tmpFilterSnippet->getContentMasterDocument() !== NULL ) {
            $tmpFilterSnippet = $tmpFilterSnippet->getContentMasterDocument();
            if($savetyFirst > 10) {
                break;
            }
            $savetyFirst++;
        }

        //add elements from Master if they don't exist in current filterSnippet
        $elementsMaster = $tmpFilterSnippet->getElements();
        if(is_array($elementsMaster) and count($elementsMaster)>0){
            foreach($elementsMaster as $key => $el){
                if(!in_array($key, array_keys($elements))){
                    $elements[$key]=$el;
                }
            }
        }

        foreach($elements as $key => $element){
            if(! $element = $filterSnippet->getElement()->getElement($key)) {
                continue;
            }
            if(!empty($specificParams)) {
                $found = false;
                foreach($specificParams as $specificParam) {
                    if(strpos($element->getName(),$specificParam . "filterblock") !== false){
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    continue;
                }
            }

            if(strpos($element->getName(),'starsListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'fo_starsfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }



            if(strpos($element->getName(),'themeListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'facilityListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'roomFacilityListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'townListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'mealtypeListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'categoryListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'fo_categoriesfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }

            if(strpos($element->getName(),'marketingGroupListfilterblock') !== false){
                foreach($element->getElements() as $el){
                    if($el) {
                        $allArray[] = $el->getId();
                    }
                }
            }


        }

        return $allArray;
    }

    // For new and sortable filter snippet
    /**
     * @param $requiredBubbles
     * @return array
     */
    protected function setupBubbles($requiredBubbles) {
        if($this->getParam("noBubbles")) {
            return array();
        }
        $params = $this->getSearchParams();
        //$this->getSearchList($params);

        /* if($this->document->getProperty("demi_isPackageSearch")) {
            //legacy? -> Demi2015_Accommodation_Search_Service_BubbleCalculatorHousePackage
            $bubbleHelper = new Demi2015_Accommodation_Search_Service_BubbleCalculatorProducts($this->getDc(), $params);
        } else*/  if($params->getIsCorridor()) {
            $bubbleHelper = new Demi2015_Accommodation_Search_Service_BubbleCalculatorCorridor($this->getDc(), $params);
        } else if ($params->getSearchType()==Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE) {
            $bubbleHelper = new Demi2015_Accommodation_Search_Service_BubbleCalculatorHousePackages($this->getDc(), $params);
        } else {
            $bubbleHelper = new Demi2015_Accommodation_Search_Service_BubbleCalculator($this->getDc(), $params);
        }

        $bubbles = array();
        if(in_array("holidayThemes",$requiredBubbles)) {
            $bubbles["holidayThemesBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("themeList")), array("holidayThemes"));
        }
        if(in_array("",$requiredBubbles)){
            $bubbles["bookableBubbleArray"] = $bubbleHelper->getCountForDestIds(array(), array("bookOnly"));
        }
        if(in_array("stars",$requiredBubbles)){
            $bubbles["starsBubbleArray"]  = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("starsList", "classifications")), array("stars", "classifications"));
        }
        if(in_array("stars_fo",$requiredBubbles)){
            $bubbles["starsBubbleArray"]  = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("fo_stars")), array("fo_stars"));
        }
        if(in_array("orte",$requiredBubbles)){
            $bubbles["townsBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("townList")), array("towns"));
        }
        if(in_array("facilities",$requiredBubbles)){
            $bubbles["facilitiesBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("facilityList")), array("facilities"), true);
        }
        if(in_array("roomFacilities",$requiredBubbles)){
            $bubbles["roomFacilitiesBubbleArray"]  = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("roomFacilityList")), array("roomFacilities"), true);
        }
        if(in_array("verpflegungsarten",$requiredBubbles)){
            $bubbles["mealsBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("mealtypeList")), array("mealTypeId"));
        }
        if(in_array("kategorien",$requiredBubbles)){
            $bubbles["categoryBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("categoryList")), array("categorySingle"));
        }
        if(in_array("kategorien_fo",$requiredBubbles)){
            $bubbles["categoryBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("fo_categories")), array("fo_categories"));
        }
        if(in_array("marketinggruppen",$requiredBubbles)) {
            $bubbles["marketingGroupsBubbleArray"] = $bubbleHelper->getCountForDestIds($this->getAllDesids(array("marketingGroupList")), array("marketingGroups"));
        }

        return $bubbles;
    }

    public function ajaxGetBubblesAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();
        foreach($this->getFilterSnippetElements() as $element){
            if(strpos($element->getName(),"blocktypefilterblock")===0){
                $requiredBubbles[]=$element->text;
            }
        }

        $bubbleCats = $this->setupBubbles($requiredBubbles);
        $bubbles = array();
        foreach($bubbleCats as $bubbleCat) {
            foreach($bubbleCat as $key => $bubbleCount) {
                $bubbles[$key] = $bubbleCount;
            }
        }
        echo json_encode($bubbles);
        exit();
    }

    public function detailEnquireAction()
    {
        if(Demi2015_Configuration_Helper::getDisableEnquiry()) {
            return false; // Just return since detailEnquireAction is called from detailAction
        }
        $this->enableLayout();
        $accoId = (int)$this->getParam("accoId");
        $packageId = $this->getParam("housePackage");



        $this->document->getProperty('enquireMailServiceProvider');
        $mailParams = $this->getAllParams();
        $filteredKeys = array_filter(array_keys($mailParams),function ($key) {return substr($key,0,1) != '_';} );
        $mailParams = array_intersect_key($mailParams,array_flip($filteredKeys));

        /** @var Demi_AccommodationServiceProvider $acco */
        $acco = Demi_AccommodationServiceProvider::getById($accoId);
        if($packageId){
            $package= Demi_HousePackageMaster::getById($packageId);
        } else {
            $package = null;
        }


        $mailParams = $this->getAllParams();
        $filteredKeys = array_filter(array_keys($mailParams),function ($key) {return substr($key,0,1) != '_';} );
        $mailParams = array_intersect_key($mailParams,array_flip($filteredKeys));
        try {
            $spamBotAddress = $this->getParam("address");
            if(!empty($spamBotAddress)) {
                throw new Exception("Address field filled out. Probably from a Bot.");
            }
            if ($this->document->getProperty('enquireMailServiceProvider') != "") {
                $fallback = $this->document->getProperty('enquiryServiceProviderFallback');
                //sending the email
                $mail = new Pimcore_Mail('utf-8');
                if(Pimcore_Tool::getClientIp() == $this->testIp) {
                    if($this->getParam("email")) {
                        $mail->addTo($this->getParam("email"));
                    } else {
                        $mail->addTo("anna.huber.elements@gmail.com");
                    }
                    $mail->disableLogging();
                } else {
                    if ( $acco->getAddress()->getEmail() )
                    {
                        $mail->addTo($acco->getAddress()->getEmail());
                    }
                    else if ( $fallback != "" )
                    {
                        $fallbacks = explode(",", $fallback);
                        foreach ( $fallbacks as $fallback )
                        {
                            $mail->addTo($fallback);
                        }
                    }
                    else
                    {
                        throw new Exception("can not sent enquiry - no email on acco $accoId and no property enquiryServiceProviderFallback !");

                    }

                }

                $mail->setReplyTo($this->getParam("email"));

                $mail->setDocument($this->document->getProperty('enquireMailServiceProvider'));
                $mail->setParams($mailParams);
                $mail->setParam("searchParams", $this->getSearchParams());
                $mail->setParam("acco", $acco);
                $mail->setParam("package", $package);

                $this->logEnquire($acco);

                $mail->send();
                $mailSent = true;

            } else {
                throw new Exception("missing property on enquiryServiceProvider {$this->document}!");
            }
        } catch (Exception $e) {
            if($e->getMessage() != "Address field filled out. Probably from a Bot.") {
                $mail = new Pimcore_Mail();
                $mail->setSubject("Fehler direktanfrage ");
                $mail->addTo("deskline@elements.at");
                $mail->setBodyText($e->getMessage());
                $mail->send();
            }
        }

        if ($this->document->getProperty('enquireCustomer') != "") {
            //sending the email
            $mail = new Pimcore_Mail('utf-8');
            $mail->addTo($this->getParam("email"));
            //$mail->addTo($acco->getAddress()->getEmail());
            $mail->setDocument($this->document->getProperty('enquireCustomer'));
            $mail->setParams($mailParams);
            $mail->setParam("searchParams", $this->getSearchParams());
            $mail->setParam("acco", $acco);
            $mail->setParam("package", $package);
            if(Pimcore_Tool::getClientIp() == $this->testIp) {
                $mail->disableLogging();
            }
            $mail->send();
        }
        $this->view->enquirySent = true;


    }



    protected function logEnquire($acco) {
        try {
            $params = $this->getSearchParams();
            $loggerParams = $this->getRequest()->getPost();
            $rooms = $params->getRoomrows();
            $prodsObj = json_decode($this->getParam('productinfos'));
            if(is_array($prodsObj) && count($prodsObj) > 0) {
                foreach ( $prodsObj as $prod )
                {
                    $product = \Pimcore\Model\Object\DemiAccommodationProduct::getById($prod->id);
                    if(!empty($product)) {
                        $products[] = $product;
                        $productCount[] = (int)$prod->units;
                    }
                }
            }
            $loggerParams['products'] = $products;
            $loggerParams['productUnits'] = $productCount;

            if($loggerParams['salutation']) {
                if($salutationObj = \Pimcore\Model\Object\DemiSalutation::getById((int)$loggerParams['salutation']))
                {
                    $loggerParams['salutation'] = $salutationObj->getTitle();
                }
            }

            if( $loggerParams['country'] ) {
                if( $countryObj = \Pimcore\Model\Object\DemiGuestCountry::getById( (int)$loggerParams['country'] ) ) {
                    $loggerParams['country'] = $countryObj->getName();
                }
            }

            if( $loggerParams['mealTypeId'] ) {
                if( $mealTypeObj = \Pimcore\Model\Object\DemiMealType::getById( (int)$loggerParams['mealTypeId'] ) ) {
                    $loggerParams['mealType'] = $mealTypeObj->getText();
                }
            }

            $regions = implode(',', array_map(function($reg) {
                $region = \Pimcore\Model\Object\DemiTown::getById((int)$reg);
                return !empty($region)?$region->getName():'-';
            }, $params->getRegions()?:[]));

            $towns = implode(',', array_map(function($tow) {
                $town = \Pimcore\Model\Object\DemiTown::getById((int)$tow);
                return !empty($town)?$town->getName():'-';
            }, $params->getTowns()?:[]));

            $stars = implode(',', array_map(function($st) {
                $star = \Pimcore\Model\Object\DemiStars::getById((int)$st);
                return !empty($star)?$star->getName('de'):'-';
            }, $params->getStars()?:[]));

            $categories = implode(',', array_map(function($cat) {
                $category = \Pimcore\Model\Object\DemiCategory::getById((int)$cat);
                return !empty($category)?$category->getName('de'):'-';
            }, $params->getCategories()?:[]));

            $facilities = implode(',', array_map(function($fac) {
                $facility = \Pimcore\Model\Object\DemiFacility::getById((int)$fac);
                return !empty($facility)?$facility->getName('de'):'-';
            }, $params->getFacilities()?:[]));

            $marketingGroups = implode(',', array_map(function($grp){
                    $group = Demi_MarketingGroup::getById((int)$grp);
                    return !empty($group)?$group->getName('de'):'-';
                }, $params->getMarketingGroups()?:[])
            );
            $nights = $params->getNights();
            if(count($nights > 0)) {
                $nights = first($nights) . '-' . $nights[count($nights)-1];
            } else {
                $nights = $params->getDateFrom() - $params->getDateTo();
            }
            $result = array_merge($loggerParams, [
                'accommodation' => $acco,
                'lastname' => $loggerParams['surname'],
                'nights' => (string)$nights,
                'nacht' => $nights,
                'room_adults_1' => isset($rooms[0])?$rooms[0]->getAdults():0,
                'room_adults_2' => isset($rooms[1])?$rooms[1]->getAdults():0,
                'room_adults_3' => isset($rooms[2])?$rooms[2]->getAdults():0,
                'room_children_1' => isset($rooms[0])?count($rooms[0]->getChildAges()):0,
                'room_children_2' => isset($rooms[1])?count($rooms[1]->getChildAges()):0,
                'room_children_3' => isset($rooms[2])?count($rooms[2]->getChildAges()):0,
                'children_ages_1' => isset($rooms[0])?$rooms[0]->getChildAges():0,
                'children_ages_2' => isset($rooms[1])?$rooms[1]->getChildAges():0,
                'children_ages_3' => isset($rooms[2])?$rooms[2]->getChildAges():0,
                'regions' => $regions,
                'towns' => $towns,
                'stars' => $stars,
                'categories' => $categories,
                'facilities' => $facilities,
                'marketingGroups' => $marketingGroups,
                'maxPrice' => $params->getPriceTo()
            ]);
            DataLogger_Helper::addEntry('enquiry', $result);
        } catch(Exception $e) {

        }
    }

    public function prepareCorridorMatrix($minDate) {
        $params = $this->getSearchParams();
        //override bookable  for detailpage
        $params->setBookOnly(false);
        $validNights = $this->getNightsArray();
        $accoid = $this->getParam("accoid");
        if(!$minDate) {
            $minDate = clone $params->getDateFrom();
        }
        $nrOfDays = 7;
        // only handle days when used via right left buttons on owl
        if($this->getParam("dir")) {
            if($this->getParam("dir") == "left") {
                $nrOfDays = -7;
            } else {
                $minDate->add(new DateInterval('P1D')); //add day needed as the given min date is from the last visible owl item
            }
        }
        $corridorMatrixSet = $this->getCorridorMatrix($accoid, $minDate, $nrOfDays, $validNights);
        $this->view->baseDateFrom = $corridorMatrixSet["baseDateFrom"];
        $this->view->possibleNights = $corridorMatrixSet["possibleNights"];
        $this->view->corridorMatrix = $corridorMatrixSet["corridorMatrix"];

        $cheapestWeek = $this->getCheapestPriceFromCorridorMatrix($corridorMatrixSet["corridorMatrix"], $corridorMatrixSet["possibleNights"]);
        $this->view->cheapestWeek = $cheapestWeek;
    }

    public function ajaxGetNewCorridorMatrixAction() {

        $params = $this->getSearchParams();
        $params->setAccoIds(array($this->getparam("accoid")));
        $accoId = $this->getParam("accoid");
        $localadapter = new Demi_Accommodation_Search_Service_Vacancies_Corridor($this->getDc(), $params);
        $localResultSet = Demi_Accommodation_Search_ResultSet_Accommodation::getById($accoId, $localadapter);

//        if(!$localResultSet) {
//            header("Content-Type: application/json");
//            echo json_encode(array("status" => "failed"));
//            exit();
//        }

        if($localResultSet) {
            $jsonArray = array("status" => "success");

            $minDate = $localResultSet->getMinDateFrom($params->getDateFrom());
            $minDate = $this->getMondayFromWeekWithDate($minDate);
            $this->prepareCorridorMatrix($minDate);
            $this->renderScript("/demi2015/search-accommodation/ajax-get-corridor-matrix.php");
        } else {
            //$jsonArray = array("status" => "failed");
            $jsonArray = array("status" => "success");
            $jsonArray["messagebox"] =  $this->getErrorBox($this->view->translate("demi.In Diesem Zeitraum ist die Unterkunft leider nicht verfügbar."));
            $this->prepareCorridorMatrix($params->getDateFrom());
            header("Content-Type: application/json");
            $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-corridor-matrix.php");
            echo json_encode($jsonArray);
            exit();
        }

    }

    public function getInfoBox($key) {
        return '<div class="alert alert-info"><i class="icon-info-sign icon-2x pull-left"></i><span style="font-size: 14px;">' . $key . '</span></div>';
    }

    public function getErrorBox($key) {
        return '<div class="alert alert-danger"><i class="icon-info-sign icon-2x pull-left"></i><span style="font-size: 14px;">' . $key . '</span></div>';
    }

    public function ajaxGetCorridorMatrixAction() {

        $minDate = null;
        if($this->getParam("minDate")) {
            $minDate = DateTime::createFromFormat("d.m.Y", $this->getParam("minDate"));
        }

        $this->prepareCorridorMatrix($minDate);
        if($this->getParam("currentCheapestNights") && $this->getParam("currentCheapestPrice")
            && (int) $this->getParam("currentCheapestNights") > 0 && (doubleval($this->getParam("currentCheapestPrice")) > 0
            )) {
            $cheapestParams = new stdClass();
            $cheapestParams->nights = (int) $this->getParam("currentCheapestNights");
            $cheapestParams->price = doubleval($this->getParam("currentCheapestPrice"));
            $this->view->cheapest = $cheapestParams;
        }

    }

    public function getCorridorMatrix($accoId, $minDate, $nrOfDays, $validNights = array(5, 7, 10), $giveMeEmpty = false) {
        $maxPeriod = 90;
        $newParams = $this->getSearchParams();
        $newParams->setBookOnly(false);
        $foundSomething = false;
        if(count($validNights) <= 1) {

            $period = (int) $validNights[0];
            if($period > 0) {
                $validNights = array();
                foreach($this->availableNightRanges as $avNights) {
                    if(in_array($period, $avNights)) {
                        $validNights = $avNights;
                        break;
                    }
                }
            } else {
                $period = $newParams->getPeriod();
            }
            if(empty($validNights) && $period > 0) {
                if($period - 1 > 0) {
                    $validNights[] = $period - 1;
                }
                $validNights[] = $period;
                $validNights[] = $period + 1;
                if($period - 1 <= 0) {
                    $validNights[] = $period + 2;
                }
            }

        }

        if($nrOfDays > $maxPeriod) {
            $nrOfDays = $maxPeriod;
        }

        $baseDateFrom = clone $minDate;
        if (!$nrOfDays || $nrOfDays == 0) {
            return array();
        } else if($nrOfDays < 0) {
            $minDate->sub(new DateInterval('P1D'));
            $nrOfDays = $nrOfDays * (-1);
            $baseDateFrom->sub(new DateInterval('P' . $nrOfDays . 'D'));
        }
        $corridorMatrixSet = array();

        $corridorMatrixSet["baseDateFrom"] = $baseDateFrom;
        $corridorMatrixSet["possibleNights"] = $validNights;
//        if($minDate < $newParams->getDateFrom()) {
//            $baseDateFrom = clone $newParams->getDateFrom();
//        }

        $corridorMatrix = array();
        for($i = 0; $i < $nrOfDays; $i++) {
            $dateFrom = clone $baseDateFrom;
            if($i != 0) {
                $dateFrom->add(new DateInterval('P' . $i . 'D'));
            }
            $newParams->setDateFrom($dateFrom);
            $corridorMatrixDates = array();
            foreach($validNights as $validNight) {
                $corridorMatrixDates[$validNight] = array();
                $dateTo  = clone $dateFrom;
                $dateTo->add(new DateInterval('P' . $validNight . 'D'));
                $newParams->setDateTo($dateTo);
                $newParams->setAccoIds(array($accoId));
                $newParams->setPeriod($validNight);

                //$list = $this->getSearchList($newParams);
                //$acco = $list->getAccommodation($accoId);
                if(! $giveMeEmpty) {
                    $acco = $this->prepareLocalDetailAcco($newParams, $accoId);
                }
                if($acco) {
                    $corridorMatrixDates[$validNight] = $acco->getMinPrice();
                } else {
                    $corridorMatrixDates[$validNight] = 0;
                }
            }
//            $corridorMatrix[(int) $dateFrom->format("w")]["date"] = $dateFrom;
//            $corridorMatrix[(int) $dateFrom->format("w")]["nights"] = $corridorMatrixDates;
            $corridorMatrix[$i]["date"] = $dateFrom;
            $corridorMatrix[$i]["nights"] = $corridorMatrixDates;
        }
        $corridorMatrixSet["corridorMatrix"] = $corridorMatrix;
        return $corridorMatrixSet;
    }

    public function ajaxGetCheapestForNightsAction() {
        $this->disableViewAutoRender();

        $params = $this->getSearchParams();
        $accoId = $this->getParam("accoid");
        $nights = $this->getParam("cheapestnights");

        $time1 = millitime();

        $corrDateFrom = $params->getDateFrom();
        if ($this->getParam("corrFrom")) {
            $corrDateFrom = DateTime::createFromFormat("d.m.Y", $this->getParam("corrFrom"));
        }
        $corrDateTo = $params->getDateTo();
        if ($this->getParam("corrTo")) {
            $corrDateTo = DateTime::createFromFormat("d.m.Y", $this->getParam("corrTo"));
        }

        $dateDiff = $corrDateFrom->diff($corrDateTo);
        $diff = $dateDiff->format("%a");
        $corridorMatrixCheapestForNightsSet = $this->getCorridorMatrix($accoId, $corrDateFrom, $diff, array($nights));
        $corridorMatrixCheapestForNights = $corridorMatrixCheapestForNightsSet["corridorMatrix"];
        $cheapestArr = $this->getCheapestPriceFromCorridorMatrix($corridorMatrixCheapestForNights, array($nights));
        $cheapest = current($cheapestArr);
        if($cheapest->price === null || $cheapest->price <= 0) {
            header("Content-Type: application/json");
//            $response = $this->getResponse();
//            $response->setHeader('Content-type', 'application/json');
            echo json_encode(array(
                "status" => "failed",
                "messagebox" => $this->getErrorBox($this->view->translate("demi.In Diesem Zeitraum ist die Unterkunft leider nicht verfügbar."))
            ));
            exit();
        }
        $this->view->cheapest = $cheapest;
        $minDate = $this->getMondayFromWeekWithDate(clone $cheapest->date);
        //$this->prepareCorridorMatrix(clone $cheapest["date"]->sub(new DateInterval("P1D")));
        $this->prepareCorridorMatrix($minDate);

        $jsonArray["status"] = "success";
        $jsonArray["minprice"] = $cheapest->price;
        $jsonArray["nights"] = $cheapest->nights;
        $jsonArray["html"] = $this->view->render("/demi2015/search-accommodation/ajax-get-corridor-matrix.php");
        header("Content-Type: application/json");
        echo json_encode($jsonArray);
        exit();


        //$this->renderScript("/demi/search-accommodation/ajax-get-corridor-matrix.php");
        //$content = $this->view->template("/demi/search-accommodation/ajax-get-corridor-matrix.php", array(), false, true);

        //        $cheapestJson = json_encode($cheapest);
//        echo $cheapestJson;
//        die();
    }

    /**
     * @param $params Demi_Accommodation_Search_Parameter
     */
    public function getCorridorSearchList($params)
    {
        //$period = $this->getParam("nights");
        //$params->setPeriod((int) $period);
        $params->setIsCorridor(true);
        $dc = $this->getDc();
        //$params->setPriceFrom(null);
        //$params->setBookOnly(true);
        $adapter = new Demi_Accommodation_Search_Service_Vacancies_Corridor($dc, $params);
        $list = new Demi_Accommodation_Search_List_VacancyLocal($dc, $params, $adapter);

        if($params->getSearchType() == Demi_Accommodation_Search_Parameter::SEARCH_TYPE_EACH_PRODUCT_SEPARATED) {
            $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PRODUCTS);
        } else if ($params->getSearchType() == Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE) {
            $list->setReturnType(Demi_Accommodation_Search_List_VacancyLocal::RETURNTYPE_PACKAGES);
        }

        //p_r($list->detailSearch());die();
        $page = 1;
        if ($this->getParam("page")) {
            $page = $this->getParam("page");
        }

        if ($this->getParam("isAdditional")) {
            $pagesize = max(5, 10 - $params->getPreviousResults());
            //sleep(20);
        } else {
            $pagesize = 10;
        }

        $paginator = Zend_Paginator::factory($list);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($pagesize);

        //$this->view->filterDefinitionObject = $filterDefinition;
        //$this->view->filterService = $filterService;
        $this->view->list = $list;
        $this->view->searchParams = $params;
        $this->view->dc = $dc;
        $this->view->paginator = $paginator;
        return $list;
    }

    public function corridorListAction() {
        $this->enableLayout();
        $params = $this->getSearchParams();
        //$params->setBookOnly(true);

        //$params->setOrderKey(array("random"));
        //$time1 = millitime();
        $this->getCorridorSearchList($params);
        //p_r(millitime() - $time1);die();
        //$dc = $this->getDc();
        //$params->setPriceFrom(null);

        //$adapter = new Demi_Accommodation_Search_Service_Vacancies_Corridor($dc, $params);
        //$resultbuilder = $adapter->getResultBuilder();

        $this->renderScript("/demi2015/search-accommodation/list.php");
    }

    public function ajaxGetLocalCorridorProductsAction() {
        $this->disableViewAutoRender();
        $resultsetcachekey = $this->getParam("resultsetcachekey");
        $resultSet = Pimcore_Model_Cache::load($resultsetcachekey);
        $params = $this->getSearchParams();
        $accoId = $this->getParam("accoId");
        if(! $resultSet) {
            $localadapter = new Demi_Accommodation_Search_Service_Vacancies_Corridor($this->getDc(), $params);
            $resultSet = Demi_Accommodation_Search_ResultSet_Accommodation::getById($accoId, $localadapter);
        }
        $this->view->resultSet = $resultSet;
        $this->view->params = $params;
        $this->view->searchParams = $params;
        $this->renderScript("/demi2015/search-accommodation/includes/get-local-corridor-products.php");
    }


}
