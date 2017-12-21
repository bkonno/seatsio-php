<?php

namespace Seatsio\Events;

use JsonMapper;
use Seatsio\PageFetcher;

class Events
{

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    private $pageSize;

    public function __construct($client, $pageSize)
    {
        $this->client = $client;
        $this->pageSize = $pageSize;
    }

    /**
     * @param $chartKey string
     * @param $eventKey string
     * @param $bookWholeTables boolean
     * @return Event
     */
    public function create($chartKey, $eventKey = null, $bookWholeTables = null)
    {
        $request = new \stdClass();
        $request->chartKey = $chartKey;
        if ($eventKey !== null) {
            $request->eventKey = $eventKey;
        }
        if ($bookWholeTables !== null) {
            $request->bookWholeTables = $bookWholeTables;
        }
        $res = $this->client->request('POST', '/events', ['json' => $request]);
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = new JsonMapper();
        return $mapper->map($json, new Event());
    }

    /**
     * @param $key string
     * @return Event
     */
    public function retrieve($key)
    {
        $res = $this->client->request('GET', '/events/' . $key);
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = new JsonMapper();
        return $mapper->map($json, new Event());
    }

    /**
     * @param $key string
     * @param $chartKey string
     * @param $eventKey string
     * @param $bookWholeTables string
     * @return void
     */
    public function update($key, $chartKey = null, $eventKey = null, $bookWholeTables = null)
    {
        $request = new \stdClass();
        if ($chartKey !== null) {
            $request->chartKey = $chartKey;
        }
        if ($eventKey !== null) {
            $request->eventKey = $eventKey;
        }
        if ($bookWholeTables !== null) {
            $request->bookWholeTables = $bookWholeTables;
        }
        $this->client->request('POST', '/events/' . $key, ['json' => $request]);
    }

    /**
     * @return EventLister
     */
    public function lister()
    {
        return new EventLister(new PageFetcher('/events', $this->client, $this->pageSize, function () {
            return new EventPage();
        }));
    }

    /**
     * @param $key string
     * @param $objects string[]
     * @param $categories string[]
     * @return void
     */
    public function markAsForSale($key, $objects = null, $categories = null)
    {
        $request = new \stdClass();
        if ($objects !== null) {
            $request->objects = $objects;
        }
        if ($categories !== null) {
            $request->categories = $categories;
        }
        $this->client->request('POST', '/events/' . $key . '/actions/mark-as-for-sale', ['json' => $request]);
    }

    /**
     * @param $key string
     * @param $objects string[]
     * @param $categories string[]
     * @return void
     */
    public function markAsNotForSale($key, $objects = null, $categories = null)
    {
        $request = new \stdClass();
        if ($objects !== null) {
            $request->objects = $objects;
        }
        if ($categories !== null) {
            $request->categories = $categories;
        }
        $this->client->request('POST', '/events/' . $key . '/actions/mark-as-not-for-sale', ['json' => $request]);
    }

    /**
     * @param $key string
     * @return void
     */
    public function markEverythingAsForSale($key)
    {
        $this->client->request('POST', '/events/' . $key . '/actions/mark-everything-as-for-sale');
    }

    /**
     * @param $key string
     * @param $object string
     * @param $extraData object
     * @return void
     */
    public function updateExtraData($key, $object, $extraData)
    {
        $request = new \stdClass();
        $request->extraData = $extraData;
        $this->client->request(
            'POST',
            '/events/' . $key . '/objects/' . $object . '/actions/update-extra-data',
            ['json' => $request]
        );
    }

    /**
     * @param $key string
     * @param $object string
     * @return ObjectStatus
     */
    public function getObjectStatus($key, $object)
    {
        $res = $this->client->request('GET', '/events/' . $key . '/objects/' . $object);
        $json = \GuzzleHttp\json_decode($res->getBody());
        $mapper = new JsonMapper();
        return $mapper->map($json, new ObjectStatus());
    }

    /**
     * @param $key string
     * @param $objectOrObjects string|string[]
     * @param $status string
     * @param $holdToken string
     * @param $orderId string
     * @return void
     */
    public function changeObjectStatus($key, $objectOrObjects, $status, $holdToken = null, $orderId = null)
    {
        $request = new \stdClass();
        $request->objects = self::normalizeObjects($objectOrObjects);
        $request->status = $status;
        if ($holdToken !== null) {
            $request->holdToken = $holdToken;
        }
        if ($orderId !== null) {
            $request->orderId = $orderId;
        }
        $this->client->request(
            'POST',
            '/events/' . $key . '/actions/change-object-status',
            ['json' => $request]
        );
    }

    private static function normalizeObjects($objectOrObjects)
    {
        if (is_array($objectOrObjects)) {
            return array_map(function ($object) {
                return ["objectId" => $object];
            }, $objectOrObjects);
        }
        return [["objectId" => $objectOrObjects]];
    }

}