<?php

namespace Coral\CoreBundle\Tests\Utility;

use Coral\CoreBundle\Utility\JsonParser;

class JsonParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testImportStringException()
    {
        $request = new JsonParser;
        $request->importString('');
    }

    public function testJsonDecodeIntegerOverflow()
    {
        /*
         * this causes in a regular json_decode a notification problem
         * this is a test to check parsing errors work properly
         */
        $request = new JsonParser;
        $request->importString('{ "a": 9223372036854775807 }');
    }

    public function testConstructEmpty()
    {
        $request = new JsonParser;
        $this->assertTrue(is_array($request->getParams()));
        $this->assertCount(0, $request->getParams());
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testInvalidJson()
    {
        $request = new JsonParser('{');
    }

    public function testImportString()
    {
        $request = new JsonParser;
        $params  = $request->importString('{ "event":"add_content", "url": "http://www.orm-designer.com/flush-content-cache?node=$SLUG$" }');

        $this->assertCount(2, $params);
        $this->assertArrayHasKey('event', $params);
        $this->assertArrayHasKey('url', $params);
        $this->assertEquals('add_content', $params['event']);
        $this->assertEquals('http://www.orm-designer.com/flush-content-cache?node=$SLUG$', $params['url']);
    }

    public function testHasParam()
    {
        $request = new JsonParser('{ "events": { "add_content": false, "no": [0, 1] } }');
        $this->assertTrue($request->hasParam('events.add_content'));
        $this->assertFalse($request->hasParam('events.none'));
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamException()
    {
        $request = new JsonParser('{ "event":"add_content", "url": "http://www.orm-designer.com/flush-content-cache?node=$SLUG$" }');
        $request->getMandatoryParam('test');
    }

    public function testGetMandatoryParam()
    {
        $request = new JsonParser('{ "event":"add_content", "url": "http://www.orm-designer.com/flush-content-cache?node=$SLUG$" }');
        $this->assertEquals('add_content', $request->getMandatoryParam('event'));
    }

    public function testGetOptionalParam()
    {
        $request = new JsonParser('{ "event":"add_content", "url": "http://www.orm-designer.com/flush-content-cache?node=$SLUG$" }');
        $this->assertEquals('add_content', $request->getOptionalParam('event'));
        $this->assertFalse($request->getOptionalParam('none'));
    }

    public function testGetMandatoryParamArray()
    {
        $request = new JsonParser('{ "events": ["event1", "event2"], "url": "someurl" }');
        $this->assertEquals('event1', $request->getMandatoryParam('events[0]'));
        $this->assertEquals('event2', $request->getMandatoryParam('events[1]'));
        $this->assertEquals('someurl', $request->getMandatoryParam('url'));
        $this->assertCount(2, $request->getMandatoryParam('events'));
    }

    public function testGetMandatoryParamAssociativeArray()
    {
        $request = new JsonParser('{ "events": {"event1": 1, "event2": 3}, "url": "someurl" }');
        $this->assertEquals(1, $request->getMandatoryParam('events.event1'));
        $this->assertEquals(3, $request->getMandatoryParam('events.event2'));
    }

    public function testGetMandatoryParamAssociativeArrayLevel3()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, "event2"], "url": "someurl" }');
        $this->assertEquals('value', $request->getMandatoryParam('events[0].event1.key'));
        $this->assertEquals('event2', $request->getMandatoryParam('events[1]'));
    }

    public function testGetMandatoryParamWildcardArrayLevel3()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }, "event3": { "key": "value3", "newkey": "value4" }}, { "event2": { "key": "value2" }}], "url": "someurl" }');
        $this->assertCount(3, $request->getMandatoryParam('events.*.*.key'));
        $this->assertEquals('value', $request->getMandatoryParam('events.*.*.key[0]'));
        $this->assertEquals('value3', $request->getMandatoryParam('events.*.*.key[1]'));
        $this->assertEquals('value2', $request->getMandatoryParam('events.*.*.key[2]'));
        $this->assertCount(1, $request->getMandatoryParam('events.*.*.newkey'));
        $this->assertEquals('value4', $request->getMandatoryParam('events.*.*.newkey[0]'));

        $wildcard = $request->getMandatoryParam('*');
        $this->assertEquals('someurl', $wildcard['url']);

        $wildcard = $request->getMandatoryParam('events.*.*.*');
        $this->assertEquals('value2', $wildcard['key'][2]);
        $this->assertEquals('value4', $wildcard['newkey'][0]);
    }

    public function testGetMandatoryParamWildcardArrayGeneric()
    {
        $request = new JsonParser('{ "events": [ "event1", "event2" ], "url": "someurl" }');
        $this->assertCount(2, $request->getMandatoryParam('events.*'));
        $events = $request->getMandatoryParam('events.*');
        $this->assertEquals('event1', $events[0]);
        $this->assertEquals('event2', $events[1]);
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamArrayException()
    {
        $request = new JsonParser('{ "events": ["event1", "event2"], "url": "someurl" }');
        $this->assertEquals('add_content', $request->getMandatoryParam('events[2]'));
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamArrayLevel3Exception()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, "event2"], "url": "someurl" }');
        $request->getMandatoryParam('events[0].event1.unknown');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamWildcardArrayLevel3Exception()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, "event2"], "url": "someurl" }');
        $request->getMandatoryParam('events.*.key[2]');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamInvalidException()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, "event2"], "url": "someurl" }');
        $request->getMandatoryParam('events.[*].key[0]');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamInvalid2Exception()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, "event2"], "url": "someurl" }');
        $request->getMandatoryParam('events[.key[0]');
    }

    /**
     * @expectedException Coral\CoreBundle\Exception\JsonException
     */
    public function testGetMandatoryParamInvalid3Exception()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, "event2"], "url": "someurl" }');
        $request->getMandatoryParam('events].key[0]');
    }

    public function testGetOptionalParamWildcardArrayLevel3()
    {
        $request = new JsonParser('{ "events": [{ "event1": { "key": "value" }}, { "event2": { "key": "value2" }}], "url": "someurl" }');
        $this->assertCount(2, $request->getOptionalParam('events.*.*.key'));
        $this->assertEquals('value', $request->getOptionalParam('events.*.*.key[0]'));
        $this->assertEquals('value2', $request->getOptionalParam('events.*.*.key[1]'));
        $this->assertFalse($request->getOptionalParam('events.*.key[2]'));
        $this->assertFalse($request->getOptionalParam('events.*.*.key[2]'));
        $this->assertFalse($request->getOptionalParam('events.invalid.key[2]'));
        $this->assertFalse($request->getOptionalParam('events[0].key[2]'));
        $this->assertEquals('default_value', $request->getOptionalParam('events[0].key[2]', 'default_value'));
    }
}