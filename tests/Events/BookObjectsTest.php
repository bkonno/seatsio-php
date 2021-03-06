<?php

namespace Seatsio\Events;

use Seatsio\SeatsioClientTest;

class BookObjectsTest extends SeatsioClientTest
{

    public function test()
    {
        $chartKey = $this->createTestChart();
        $event = $this->seatsioClient->events->create($chartKey);

        $res = $this->seatsioClient->events->book($event->key, ["A-1", "A-2"]);

        self::assertEquals(ObjectStatus::$BOOKED, $this->seatsioClient->events->retrieveObjectStatus($event->key, "A-1")->status);
        self::assertEquals(ObjectStatus::$BOOKED, $this->seatsioClient->events->retrieveObjectStatus($event->key, "A-2")->status);

        self::assertEquals([
            "A-1" => someLabels("1", "seat", "A", "row"),
            "A-2" => someLabels("2", "seat", "A", "row")
        ], $res->labels);
    }

    public function testSections()
    {
        $chartKey = $this->createTestChartWithSections();
        $event = $this->seatsioClient->events->create($chartKey);

        $res = $this->seatsioClient->events->book($event->key, ["Section A-A-1", "Section A-A-2"]);

        self::assertEquals(ObjectStatus::$BOOKED, $this->seatsioClient->events->retrieveObjectStatus($event->key, "Section A-A-1")->status);
        self::assertEquals(ObjectStatus::$BOOKED, $this->seatsioClient->events->retrieveObjectStatus($event->key, "Section A-A-2")->status);

        self::assertEquals([
            "Section A-A-1" => someLabels("1", "seat", "A", "row", "Section A", "Entrance 1"),
            "Section A-A-2" => someLabels("2", "seat", "A", "row", "Section A", "Entrance 1")
        ], $res->labels);
    }

    public function testHoldToken()
    {
        $chartKey = $this->createTestChart();
        $event = $this->seatsioClient->events->create($chartKey);
        $holdToken = $this->seatsioClient->holdTokens->create();
        $this->seatsioClient->events->hold($event->key, "A-1", $holdToken->holdToken);

        $this->seatsioClient->events->book($event->key, "A-1", $holdToken->holdToken);

        $objectStatus = $this->seatsioClient->events->retrieveObjectStatus($event->key, "A-1");
        self::assertEquals(ObjectStatus::$BOOKED, $objectStatus->status);
        self::assertNull($objectStatus->holdToken);
    }

    public function testOrderId()
    {
        $chartKey = $this->createTestChart();
        $event = $this->seatsioClient->events->create($chartKey);

        $this->seatsioClient->events->book($event->key, "A-1", null, "order1");

        $objectStatus = $this->seatsioClient->events->retrieveObjectStatus($event->key, "A-1");
        self::assertEquals("order1", $objectStatus->orderId);
    }

}