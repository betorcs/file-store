<?php

use PHPUnit\Framework\TestCase;

class LocalFileStoreTest extends TestCase
{

    private $fileStore;

    function setUp()
    {
        $this->fileStore = new LocalFileStore('/tmp');
    }

    function tearDown()
    {
        $this->fileStore->clean();
    }

    public function testStoreAndRecoverAnyContentLocally()
    {
        $data = 'foo';

        $key = $this->fileStore->store($data, 0);
        $this->assertNotNull($key);

        $this->assertEquals($data, $this->fileStore->restore($key));
    }

    public function testStoreAndRecoverImageLocally()
    {
        $data = $this->getImageContent('tests/resources/land.jpeg');
        $key = $this->fileStore->store($data, 0);
        $this->assertNotNull($key);

        $this->assertEquals($data, $this->fileStore->restore($key));
    }

    /**
     * @expectedException Betorcs\FileStoreException
     * @expectedExceptionMessage No file found for given key
     */
    public function testDeleteKey()
    {
        $data = 'foo';

        $key = $this->fileStore->store($data, 0);
        $this->assertNotNull($key);

        $this->fileStore->delete($key);

        $this->fileStore->restore($key);
    }

    /**
     * @expectedException Betorcs\FileStoreException
     * @expectedExceptionMessage No file found for given key
     */
    public function testCleanKey()
    {
        $data = 'foo';
        $key = $this->fileStore->store($data, 0);
        $this->assertNotNull($key);

        $this->fileStore->clean();

        $this->fileStore->restore($key);
    }

    public function testDeleteAllExpiredKey()
    {
        $expired = 'expired';
        $expiredKey = $this->fileStore->store($expired, 1);
        sleep(2);

        $notExpired = 'notExpired';
        $notExpiredKey = $this->fileStore->store($notExpired, 0);

        $this->fileStore->deleteAllExpired();

        $this->assertTrue($this->fileStore->exists($notExpiredKey));
        $this->assertFalse($this->fileStore->exists($expiredKey));
    }

    /**
     * @expectedException Betorcs\FileStoreException
     * @expectedExceptionMessage File expired
     */
    public function testRecoverExpiredFileShouldThrowException()
    {
        $data = $this->getImageContent('tests/resources/land.jpeg');
        $key = $this->fileStore->store($data, 2);
        $this->assertNotNull($key);
        sleep(3);
        $this->assertEquals($data, $this->fileStore->restore($key));
    }

    private function getImageContent($filename)
    {
        $h = fopen($filename, 'r');
        $content = fread($h, filesize($filename));
        fclose($h);
        return $content;
    }
}
