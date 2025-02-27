<?php

declare(strict_types=1);

namespace EventSauce\EventSourcing\Serialization;

use EventSauce\EventSourcing\PayloadStub;
use PHPStan\Testing\TestCase;
use stdClass;

final class TypeValidatingEventSerializerTest extends TestCase
{
    /**
     * @var TypeValidatingPayloadSerializer
     */
    private $serializer;

    /**
     * @var ConstructingPayloadSerializer
     */
    private $innerSerializer;

    public function setUp(): void
    {
        $this->innerSerializer = new ConstructingPayloadSerializer();
        $this->serializer = new TypeValidatingPayloadSerializer(
            $this->innerSerializer,
            SerializablePayload::class
        );
    }

    /**
     * @test
     */
    public function is_an_event_serializer(): void
    {
        $this->assertInstanceOf(PayloadSerializer::class, $this->serializer);
    }

    /**
     * @test
     */
    public function delegates_serialization_to_decorated_serializer(): void
    {
        $event = PayloadStub::create('some value');
        $this->assertSame(
            $this->innerSerializer->serializePayload($event),
            $this->serializer->serializePayload($event)
        );
    }

    /**
     * @test
     */
    public function delegates_unserialization_to_decorated_serializer(): void
    {
        $payloadArgs = [PayloadStub::class, ['value' => 'some value']];

        $this->assertSame(
            $this->innerSerializer->unserializePayload(...$payloadArgs)->toPayload(),
            $this->serializer->unserializePayload(...$payloadArgs)->toPayload()
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot serialize event that does not implement "EventSauce\EventSourcing\Serialization\SerializablePayload".
     */
    public function cannot_serialize_non_instance_of_provided_event_classname(): void
    {
        $this->serializer->serializePayload(new stdClass());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot unserialize payload into an event that does not implement "EventSauce\EventSourcing\Serialization\SerializablePayload".
     */
    public function cannot_unserialize_into_non_serializable_event(): void
    {
        $this->serializer->unserializePayload(stdClass::class, ['value' => 'some value']);
    }
}
