<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DAL;

/**
 * Description of HighLevelDALTest
 *
 * @author Guy
 */
class HighLevelDALTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group HighLevelDALTest
     */
    public function testAutomaticId()
    {
        $data = new DummyDataObject();
        $this->assertEquals(23, strlen($data->getId()));
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoad()
    {
        $data = new DummyDataObject();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 0);
        $loadedData = $dal->loadDataObject($data->getCombinedId(), 0);
        $this->assertEquals($data->simpleArray, $loadedData->simpleArray, "Error: saving and loading simple arrray failed");
        $this->assertEquals($data->integerValue, $loadedData->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($data->stringValue, $loadedData->stringValue, "Error: saving and loading string value failed");
        $this->assertEquals("", $loadedData->donotsave, "Error: donotsave annotation faild");
    }

    
    /**
     * @group HighLevelDALTest
     */
    public function testSaveUpdateAndLoad()
    {
        $data = new DummyDataObject();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 0);
        $data->integerValue = 2020;
        $dal->updateDataObject($data, 0);
        
        $loadedData = $dal->loadDataObject($data->getCombinedId(), 0);
        $this->assertEquals($data->simpleArray, $loadedData->simpleArray, "Error: saving and loading simple arrray failed");
        $this->assertEquals($data->integerValue, $loadedData->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($data->stringValue, $loadedData->stringValue, "Error: saving and loading string value failed");
        $this->assertEquals("", $loadedData->donotsave, "Error: donotsave annotation faild");
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadWrongDepth()
    {
        $data = new DummyDataObject();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 10);
        $loadedData = $dal->loadDataObject($data->getCombinedId(), 10);
        $this->assertEquals($data->simpleArray, $loadedData->simpleArray, "Error: saving and loading simple arrray failed");
        $this->assertEquals($data->integerValue, $loadedData->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($data->stringValue, $loadedData->stringValue, "Error: saving and loading string value failed");
        $this->assertEquals("", $loadedData->donotsave, "Error: donotsave annotation faild");
    }

    /**
     * @group HighLevelDALTest
     * @expectedException Exception
     * @expectedExceptionMessage Only \DAL\DataObject can be saved
     */
    public function testSaveWrongObject()
    {
        $data = new \stdClass();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 0);
    }

    /**
     * @group HighLevelDALTest
     * @expectedException Exception
     * @expectedExceptionMessage DataObject not found ["DummyDataObject","123456"]   
     */
    public function testLoadNotExistingObject()
    {
        $data = new DummyDataObject();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 0);
        $dal->loadDataObject(['DummyDataObject', '123456'], 0);
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndDeleteDataObject()
    {
        $data = new DummyDataObject();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 0);
        $cid = $data->getCombinedId();
        $this->assertTrue($dal->isDataObjectExists($cid));
        $dal->deleteDataObject($data,0);
        $cid = $data->getCombinedId();
        $this->assertFalse($dal->isDataObjectExists($cid));
    }

    
    /**
     * @group HighLevelDALTest
     * @expectedException Exception
     * @expectedExceptionMessage Table not found: NoSuchClass
     */
    public function testLoadNotExistingClass()
    {
        $dal = new \DAL\HighLevelDAL();
        $dal->loadDataObject(['NoSuchClass', '123456'], 0);
    }

    /**
     * @group HighLevelDALTest     
     */
    public function testCreateFromArray()
    {
        $dal = new \DAL\HighLevelDAL();
        $arr = ['simpleArray' => json_encode(['txt', 101]),
            'integerValue' => 220,
            'stringValue' => 'my str',
            'donotsave' => 'do not save string',
            'notExistingField' => 'some data'
        ];

        $data = $dal->createDataObject('\DAL\DummyDataObject', $arr);
        $this->assertEquals(23, strlen($data->getId()), 'no automatic id created');
        $this->assertEquals(['txt', 101], $data->simpleArray, "Error: saving and loading simple arrray failed");
        $this->assertEquals(220, $data->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals('my str', $data->stringValue, "Error: saving and loading string value failed");
        $this->assertEquals('do not save string', $data->donotsave, "Error: donotsave annotation faild");
        $r = new \ReflectionClass($data);
        $this->assertFalse($r->hasProperty('notExistingField'));
    }

    /**
     * @group HighLevelDALTest   
     * @expectedException Exception
     * @expectedExceptionMessage Not a data object class name provided
     */
    public function testCreateBadClassName()
    {
        $dal = new \DAL\HighLevelDAL();
        $arr = ['integerValue' => 220,
            'stringValue' => 'my str'];
        $dal->createDataObject('stdClass', $arr);
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadNotStorable()
    {
        $data = new DummyNotStorableDataObject();
        $dal = new \DAL\HighLevelDAL();
        $dal->saveDataObject($data, 0);
        $loadedData = $dal->loadDataObject($data->getCombinedId(), 0);
        $this->assertEquals($data->name, $loadedData->name);
        $this->assertEquals($data->dummyClass, $loadedData->dummyClass);
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadSingleConnector()
    {
        $dal = new \DAL\HighLevelDAL();

        $d = new DummyDataObject();
        $data = new DummyDataWithConnector();
        $data->connectData($d);

        $dal->saveDataObject($data, 0);
        $loadedData = $dal->loadDataObject($data->getCombinedId(), 0);
#        $this->assertEquals($data->data, $loadedData->data);
        $this->assertEquals($data->getId(), $loadedData->getId());
        $this->assertEquals($data->getDataCombinedId(), $loadedData->getDataCombinedId());
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadSingleConnectorDepth1()
    {
        $dal = new \DAL\HighLevelDAL();

        $d = new DummyDataObject();
        $data = new DummyDataWithConnector();
        $data->connectData($d);

        $dal->saveDataObject($data, 1);
        $loadedData = $dal->loadDataObject($data->getCombinedId(), 1);
        $this->assertEquals($data->getId(), $loadedData->getId());
        $this->assertEquals($data->getDataCombinedId(), $loadedData->getDataCombinedId());
        $this->assertEquals($d->simpleArray, $loadedData->data->simpleArray, "Error: saving and loading array failed");
        $this->assertEquals($d->integerValue, $loadedData->data->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($d->stringValue, $loadedData->data->stringValue, "Error: saving and loading string value failed");
        $this->assertEquals("", $loadedData->data->donotsave, "Error: donotsave annotation faild");
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadSingleConnectorDepth2()
    {
        $dal = new \DAL\HighLevelDAL();

        $ddo = new DummyDataObject();
        $ddwc1 = new DummyDataWithConnector();
        $ddwc2 = new DummyDataWithConnector();
        $ddwc2->connectData($ddwc1);
        $ddwc1->connectData($ddo);

        $dal->saveDataObject($ddwc2, 2);
        $loadedData = $dal->loadDataObject($ddwc2->getCombinedId(), 2);

        $data = $ddwc2;
        $this->assertEquals($data->getId(), $loadedData->getId());
        $this->assertEquals($data->getDataCombinedId(), $loadedData->getDataCombinedId());
        $d = $ddo;
        $this->assertEquals($d->simpleArray, $loadedData->data->data->simpleArray, "Error: saving and loading array failed");
        $this->assertEquals($d->integerValue, $loadedData->data->data->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($d->stringValue, $loadedData->data->data->stringValue, "Error: saving and loading string value failed");
        $this->assertEquals("", $loadedData->data->data->donotsave, "Error: donotsave annotation faild");
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadNotStorableSingleConnector()
    {
        $dal = new \DAL\HighLevelDAL();

        $dnswc = new DummyNotStorableWithConnector();

        $dal->saveDataObject($dnswc, 0);
        $loadedDnswc = $dal->loadDataObject($dnswc->getCombinedId(), 0);
        $this->assertEquals($dnswc->getId(), $loadedDnswc->getId());
        $this->assertEquals($dnswc->getDataCombinedId(), $loadedDnswc->getDataCombinedId());
    }

    /**
     * @group HighLevelDALTest
     */
    public function testCheckDataMultipleConnectors()
    {
        $ddwmc = new DummyDataWithMultipleConnectors();
        $this->assertEquals('DAL\DummyDataWithMultipleConnectors', get_class($ddwmc));
        $s = $ddwmc->toStorable();
        $this->assertEquals('DAL\DummyStorableWithMultipleConnectors', get_class($s));
        $d = $s->toDataObject();
        $this->assertEquals('DAL\DummyDataWithMultipleConnectors', get_class($d));
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadDataMultipleConnectors()
    {
        $dal = new \DAL\HighLevelDAL();
        $ddwmc = new DummyDataWithMultipleConnectors();
        $dal->saveDataObject($ddwmc, 0);
        $loadedDnwmc = $dal->loadDataObject($ddwmc->getCombinedId(), 0);
        $this->assertEquals('DAL\DummyDataWithMultipleConnectors', get_class($loadedDnwmc));

        $this->assertEquals($ddwmc->getId(), $loadedDnwmc->getId());
        $this->assertEquals($ddwmc->simpleArray, $loadedDnwmc->simpleArray, "Error: saving and loading simple arrray failed");
        $this->assertEquals($ddwmc->integerValue, $loadedDnwmc->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($ddwmc->stringValue, $loadedDnwmc->stringValue, "Error: saving and loading string value failed");
    }

    /**
     * @group HighLevelDALTest
     */
    public function testSaveAndLoadDataMultipleConnectorsDepth1()
    {
        $dal = new \DAL\HighLevelDAL();
        $ddwmc = new DummyDataWithMultipleConnectors();
        for ($index = 0; $index < 3; $index++) {
            $ddo = new DummyDataObject();
            $ddwmc->addConnection($ddo);
        }

        $dal->saveDataObject($ddwmc, 1);
        $loadedDnwmc = $dal->loadDataObject($ddwmc->getCombinedId(), 1);
        $this->assertEquals(get_class($ddwmc), get_class($loadedDnwmc));
        $this->assertEquals($ddwmc->getId(), $loadedDnwmc->getId());
        $this->assertEquals($ddwmc->simpleArray, $loadedDnwmc->simpleArray, "Error: saving and loading simple arrray failed");
        $this->assertEquals($ddwmc->integerValue, $loadedDnwmc->integerValue, "Error: saving and loading integer value failed");
        $this->assertEquals($ddwmc->stringValue, $loadedDnwmc->stringValue, "Error: saving and loading string value failed");

        $multiple = new \MultipleIterator();
        $multiple->attachIterator($ddwmc);
        $multiple->attachIterator($loadedDnwmc);

        foreach ($multiple as $key => $value) {
            $this->assertEquals($key[0], $key[1]);
            $this->assertEquals($value[0]->simpleArray, $value[1]->simpleArray, "Error: saving and loading simple arrray failed");
            $this->assertEquals($value[0]->integerValue, $value[1]->integerValue, "Error: saving and loading integer value failed");
            $this->assertEquals($value[0]->stringValue, $value[1]->stringValue, "Error: saving and loading string value failed");
            $this->assertEquals("", $value[1]->donotsave, "Error: donotsave annotation faild");
        }
    }
    
    /**
     * @group HighLevelDALTest
     */
      public function testCreateFromArraySingle()
    {
        $dal = new \DAL\HighLevelDAL();
        $arr = [
            'somedata' => 'my data'
        ];
        $data = $dal->createDataObject('\DAL\DummyDataWithConnector', $arr);
        $this->assertEquals(23, strlen($data->getId()), 'no automatic id created');
        $this->assertEquals('my data', $data->somedata, "Error: saving and loading string value failed");
           
    }
    
//     /**
//     * @group HighLevelDALTest1
//     */
//    public function testDataObjectToArray(){
//        $dal = new \DAL\HighLevelDAL();
//        $ddo = new DummyDataObject();
//        
//        $arr = $dal->dataObjectToArray($ddo);
//        var_dump($arr);
//    }
}
