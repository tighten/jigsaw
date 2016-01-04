<?php namespace Jigsaw\Tests;

use Illuminate\Filesystem\Filesystem;
use Jigsaw\Jigsaw\Console\InitCommand;

class InitCommandTest extends CommandTestCase
{

    /**
     * @var string
     */
    protected static $tmpPath;

    /**
     * @var Filesystem
     */
    protected static $filesystem;

    /**
     * Before the test cases are run, change directory to the tests directory and set the _tmp path
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::$filesystem = new Filesystem();
        self::$tmpPath = __DIR__ . DIRECTORY_SEPARATOR . '_tmp';
        self::$filesystem->makeDirectory(self::$tmpPath);
        chdir('tests');
    }

    /**
     * Clean the _tmp path between tests so they do not conflict with one another
     */
    protected function tearDown()
    {
        self::$filesystem->cleanDirectory(self::$tmpPath);
    }

    /**
     * Delete the _tmp path once the tests are all complete
     */
    public static function tearDownAfterClass()
    {
        self::$filesystem->deleteDirectory(self::$tmpPath);
    }

    /**
     * Test that we are within the right path for Jigsaw to be tested
     */
    public function testCurrentWorkingDirectoryIsTestTemp()
    {
        $this->assertEquals(__DIR__, getcwd());
    }

    /**
     * Test the default init command where directory does not exist
     */
    public function testDefaultInit()
    {
        chdir(self::$tmpPath);

        $output = $this->runCommand(InitCommand::class, 'init');

        $this->assertRegExp("/Site initialized successfully in/", $output->getDisplay());
        $this->assertTrue((strpos($output->getDisplay(), self::$tmpPath) !== false));
        $this->assertTrue(self::$filesystem->exists(self::$tmpPath . DIRECTORY_SEPARATOR . 'config.php'));
        $this->assertTrue(self::$filesystem->exists(self::$tmpPath . DIRECTORY_SEPARATOR . 'source'));
        $this->assertEquals($output->getStatusCode(), 0);
    }

    /**
     * Test that the command gracefully fails when asked to init in an already initiated path
     */
    public function testDefaultInitFails()
    {
        chdir(self::$tmpPath);

        $this->runCommand(InitCommand::class, 'init');
        $output = $this->runCommand(InitCommand::class, 'init');
        $this->assertRegExp("/already exists, doing nothing and exiting./", $output->getDisplay());
        $this->assertEquals($output->getStatusCode(), 1);
    }

    /**
     * Test that the command works when the name argument is passed
     */
    public function testInitRunsWithName()
    {
        chdir(self::$tmpPath);

        $output = $this->runCommand(InitCommand::class, 'init', ['name' => 'foo']);
        $this->assertTrue((strpos($output->getDisplay(), self::$tmpPath . DIRECTORY_SEPARATOR . 'foo') !== false));
        $this->assertTrue(self::$filesystem->exists(self::$tmpPath . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'config.php'));
        $this->assertTrue(self::$filesystem->exists(self::$tmpPath . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'source'));
    }
}
