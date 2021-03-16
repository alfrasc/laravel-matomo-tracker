<?php

namespace Alfrasc\MatomoTracker;

use Exception;
use \Illuminate\Http\Request;
use \MatomoTracker;

class LaravelMatomoTracker extends MatomoTracker
{

    /** @var string */
    protected $apiUrl;
    /** @var int */
    protected $idSite;
    /** @var string */
    protected $tokenAuth;
    /** @var string */
    protected $queue;

    public function __construct(?Request $request, ?int $idSite = null, ?string $apiUrl = null, ?string $tokenAuth = null)
    {
        $this->tokenAuth = $tokenAuth ?: config('matomotracker.tokenAuth');
        $this->queue = config('matomotracker.queue', 'matomotracker');

        $this->setTokenAuth(!is_null($tokenAuth) ? $tokenAuth : config('matomotracker.tokenAuth'));
        $this->setMatomoVariables($request, $idSite, $apiUrl);
    }

    /**
     * Overrides the PiwikTracker method and uses the \Illuminate\Http\Request for filling in the server vars.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $idSite
     * @param string $apiUrl
     *
     * @return void
     */
    private function setMatomoVariables(Request $request, int $idSite = null, string $apiUrl = null)
    {

        $this->apiUrl = $apiUrl ?: config('matomotracker.url');
        $this->idSite = $idSite ?: config('matomotracker.idSite');

        $this->ecommerceItems = array();
        $this->attributionInfo = false;
        $this->eventCustomVar = false;
        $this->forcedDatetime = false;
        $this->forcedNewVisit = false;
        $this->networkTime = false;
        $this->serverTime = false;
        $this->transferTime = false;
        $this->domProcessingTime = false;
        $this->domCompletionTime = false;
        $this->onLoadTime = false;
        $this->pageCustomVar = false;
        $this->ecommerceView = array();
        $this->customParameters = array();
        $this->customDimensions = array();
        $this->customData = false;
        $this->hasCookies = false;
        $this->token_auth = false;
        $this->userAgent = false;
        $this->country = false;
        $this->region = false;
        $this->city = false;
        $this->lat = false;
        $this->long = false;
        $this->width = false;
        $this->height = false;
        $this->plugins = false;
        $this->localHour = false;
        $this->localMinute = false;
        $this->localSecond = false;
        $this->idPageview = false;

        // $this->idSite = $this->idSite;
        $this->urlReferrer = !empty($request->server('HTTP_REFERER')) ? $request->server('HTTP_REFERER') : false;
        $this->pageCharset = self::DEFAULT_CHARSET_PARAMETER_VALUES;
        $this->pageUrl = self::getCurrentUrl();
        $this->ip = !empty($request->server('REMOTE_ADDR')) ? $request->server('REMOTE_ADDR') : false;
        $this->acceptLanguage = !empty($request->server('HTTP_ACCEPT_LANGUAGE')) ? $request->server('HTTP_ACCEPT_LANGUAGE') : false;
        $this->userAgent = !empty($request->server('HTTP_USER_AGENT')) ? $request->server('HTTP_USER_AGENT') : false;
        if (!empty($apiUrl)) {
            self::$URL = $this->apiUrl;
        }

        // Life of the visitor cookie (in sec)
        $this->configVisitorCookieTimeout = 33955200; // 13 months (365 + 28 days)
        // Life of the session cookie (in sec)
        $this->configSessionCookieTimeout = 1800; // 30 minutes
        // Life of the session cookie (in sec)
        $this->configReferralCookieTimeout = 15768000; // 6 months

        // Visitor Ids in order
        $this->userId = false;
        $this->forcedVisitorId = false;
        $this->cookieVisitorId = false;
        $this->randomVisitorId = false;

        $this->setNewVisitorId();

        $this->configCookiesDisabled = false;
        $this->configCookiePath = self::DEFAULT_COOKIE_PATH;
        $this->configCookieDomain = '';
        $this->configCookieSameSite = '';
        $this->configCookieSecure = false;
        $this->configCookieHTTPOnly = false;

        $this->currentTs = time();
        $this->createTs = $this->currentTs;
        $this->visitCount = 0;
        $this->currentVisitTs = false;
        $this->lastVisitTs = false;
        $this->ecommerceLastOrderTimestamp = false;

        // Allow debug while blocking the request
        $this->requestTimeout = 600;
        $this->doBulkRequests = false;
        $this->storedTrackingActions = array();

        $this->sendImageResponse = true;

        $this->visitorCustomVar = $this->getCustomVariablesFromCookie();

        $this->outgoingTrackerCookies = array();
        $this->incomingTrackerCookies = array();
    }

    /**
     * Sets the queue name
     *
     * @param string $queueName
     *
     * @return $this
     */
    public function setQueue(string $queueName)
    {
        $this->queue = $queueName;
        return $this;
    }

    /**
     * Sets a custom dimension
     *
     * @param int $customDimensionId
     * @param string $value
     *
     * @return $this
     */
    public function setCustomDimension($id, $value)
    {
        $this->setCustomTrackingParameter('dimension' . $customDimensionId, $value);
        return $this;
    }
     /**
     * Sets some custom dimensions
     *
     * @param array $customDimensions Is an array of objects with the fields 'id' and 'value'
     *
     * @return $this
     */
    public function setCustomDimensions(array $customDimensions)
    {
        foreach ($customDimensions as $key => $customDimension) {
            $this->checkCustomDimension($customDimension);
            $this->setCustomDimension($key, $customDimension);
        }

        return $this;
    }

    /** checks if custom dimension data is correct
     *
     * @param object $customDimension
     *
     * @return bool
     */
    private function checkCustomDimension(object $customDimension): bool
    {

        if (gettype($customDimension) !== 'object') {
            throw new Exception('Key is not of type object in custom dimension.');
        }

        if (property_exists($customDimension, 'id')) {
            if (gettype($customDimension->id) !== 'integer') {
                throw new Exception('Id is not of type integer in custom dimension.');
            }
        } else {
            throw new Exception('Missing property \'id\' in custom dimension.');
        }

        if (property_exists($customDimension, 'value')) {
            if (gettype($customDimension->value) !== 'string') {
                throw new Exception('Value is not of type string in custom dimension.');
            }
        } else {
            throw new Exception('Missing property \'id\' in custom dimension.');
        }

        return true;
    }

    /**
     * Sets some custom variables
     *
     * @param array $customVariables
     */
    public function setCustomVariables(array $customVariables)
    {
        foreach ($customVariables as $customVariable) {
            $this->checkCustomVariable($customVariable);

            $this->setCustomVariable($customVariable->id, $customVariable->name, $customVariable->value, property_exists($customVariable, 'scope') ? $customVariable->scope : 'visit');
        }

        return $this;
    }

    /** checks if custom variable data is correct
     *
     * @param object $customVariable
     *
     * @return bool
     */
    private function checkCustomVariable(object $customVariable): bool
    {
        if (gettype($customVariable) !== 'object') {
            throw new Exception('Key is not of type object in custom variable.');
        }

        if (property_exists($customVariable, 'id')) {
            if (gettype($customVariable->id) !== 'integer') {
                throw new Exception('Id is not of type integer in custom variable.');
            }
        } else {
            throw new Exception('Missing property \'id\' in custom variable.');
        }

        if (property_exists($customVariable, 'name')) {
            if (gettype($customVariable->name) !== 'string') {
                throw new Exception('Name is not of type string in custom variable.');
            }
        } else {
            throw new Exception('Missing property \'id\' in custom variable.');
        }

        if (property_exists($customVariable, 'value')) {
            if (gettype($customVariable->value) !== 'string') {
                throw new Exception('Value is not of type string in custom variable.');
            }
        } else {
            throw new Exception('Missing property \'id\' in custom variable.');
        }

        if (property_exists($customVariable, 'scope')) {
            if (gettype($customVariable->scope) !== 'string') {
                throw new Exception('Scope is not of type string in custom variable.');
            }

            if (!array_search($customVariable->scope, ['visit', 'page'])) {
                throw new Exception('Scope is not valid in custom variable. Use either \'visit\' or \'page\'');
            }
        }

        return true;
    }

    /** Shorthand for doTrackAction($actionUrl, 'download')
     *
     * @param string $actionUrl
     *
     * @return mixed
     */
    public function doTrackDownload(string $actionUrl)
    {
        return $this->doTrackAction($actionUrl, 'download');
    }

    /** Shorthand for doTrackAction($actionUrl, 'link')
     *
     * @param string $actionUrl
     *
     * @return mixed
     */
    public function doTrackOutlink(string $actionUrl)
    {
        return $this->doTrackAction($actionUrl, 'link');
    }

    /** Queues a pageview
     *
     * @param string $documentTitle
     *
     * @return void
     */
    public function queuePageView(string $documentTitle)
    {
        dispatch(function () use ($documentTitle) {
            $this->doTrackPageView($documentTitle);
        })
            ->onQueue($this->queue);
    }

    /** Queues an event
     *
     * @param string $category
     * @param string $action
     * @param string|bool $name
     * @param string|bool $value
     *
     * @return void
     */
    public function queueEvent(string $category, string $action, $name = false, $value = false)
    {
        dispatch(function () use ($category, $action, $name, $value) {
            $this->doTrackEvent($category, $action, $name, $value);
        })
            ->onQueue($this->queue);
    }

    /** Queues a content impression
     *
     * @param string $contentName
     * @param string $contentPiece
     * @param string|bool $contentTarget
     *
     * @return void
     */
    public function queueContentImpression(string $contentName, string $contentPiece = 'Unknown', $contentTarget = false)
    {
        dispatch(function () use ($contentName, $contentPiece, $contentTarget) {
            $this->doTrackContentImpression($contentName, $contentPiece, $contentTarget);
        })
            ->onQueue($this->queue);
    }

    /** Queues a content interaction
     *
     * @param string $interaction Like 'click' or 'copy'
     * @param string $contentName
     * @param string $contentPiece
     * @param string|bool $contentTarget
     *
     * @return void
     */
    public function queueContentInteraction(string $interaction, string $contentName, string $contentPiece = 'Unknown', $contentTarget = false)
    {
        dispatch(function () use ($interaction, $contentName, $contentPiece, $contentTarget) {
            $this->doTrackContentInteraction($interaction, $contentName, $contentPiece, $contentTarget);
        })
            ->onQueue($this->queue);
    }

    /** Queues a site search
     *
     * @param string $keyword
     * @param string $category
     * @param int|bool $countResults
     *
     * @return void
     */
    public function queueSiteSearch(string $keyword, string $category = '',  $countResults = false)
    {
        dispatch(function () use ($keyword, $category, $countResults) {
            $this->doTrackSiteSearch($keyword, $category, $countResults);
        })
            ->onQueue($this->queue);
    }

    /** Queues a goal
     *
     * @param mixed $idGoal
     * @param float $revencue
     *
     * @return void
     */
    public function queueGoal($idGoal, $revencue = 0.0)
    {
        dispatch(function () use ($idGoal, $revencue) {
            $this->doTrackGoal($idGoal, $revencue);
        })
            ->onQueue($this->queue);
    }

    /** Queues a download
     *
     * @param string $actionUrl
     *
     * @return void
     */
    public function queueDownload(string $actionUrl)
    {
        dispatch(function () use ($actionUrl) {
            $this->doTrackDownload($actionUrl);
        })
            ->onQueue($this->queue);
    }

    /** Queues a outlink
     *
     * @param string $actionUrl
     *
     * @return void
     */
    public function queueOutlink(string $actionUrl)
    {
        dispatch(function () use ($actionUrl) {
            $this->doTrackOutlink($actionUrl);
        })
            ->onQueue($this->queue);
    }

    /** Queues an ecommerce update
     *
     * @param float $grandTotal
     *
     * @return void
     */
    public function queueEcommerceCartUpdate(float $grandTotal)
    {
        dispatch(function () use ($grandTotal) {
            $this->doTrackEcommerceCartUpdate($grandTotal);
        })
            ->onQueue($this->queue);
    }

    /** Queues a ecommerce order
     *
     * @param float $orderId
     * @param float $grandTotal
     * @param float $subTotal
     * @param float $tax
     * @param float $shipping
     * @param float $discount
     *
     * @return void
     */
    public function queueEcommerceOrder(
        float $orderId,
        float $grandTotal,
        float $subTotal = 0.0,
        float $tax = 0.0,
        float $shipping = 0.0,
        float $discount = 0.0
    ) {
        dispatch(function () use (
            $orderId,
            $grandTotal,
            $subTotal,
            $tax,
            $shipping,
            $discount
        ) {
            $this->doTrackEcommerceOrder(
                $orderId,
                $grandTotal,
                $subTotal,
                $tax,
                $shipping,
                $discount
            );
        })
            ->onQueue($this->queue);
    }

    /** Queues a bulk track
     *
     * @return void
     */
    public function queueBulkTrack()
    {
        dispatch(function () {
            $this->doBulkTrack();
        })
            ->onQueue($this->queue);
    }

    /**
     * Called after unserializing (e.g. after popping from the queue). Re-set
     * self::$URL, as only non-static properties have been applied.
     */
    public function __wakeup()
    {
        if (!empty($this->apiUrl)) {
            self::$URL = $this->apiUrl;
        }
    }
}
