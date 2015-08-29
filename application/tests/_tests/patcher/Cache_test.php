<?php

namespace Kenjis\MonkeyPatch;

use CIPHPUnitTest;
use CIPHPUnitTestReflection;

/**
 * @group ci-phpunit-tests
 */
class Cache_test extends \PHPUnit_Framework_TestCase
{
	public static function tearDownAfterClass()
	{
		Cache::clearCache();
		CIPHPUnitTest::setPatcherCacheDir();
	}

	public function test_getCacheDir()
	{
		$cache_dir = APPPATH . 'tests/_ci_phpunit_test/tmp/cache_test';
		Cache::setCacheDir($cache_dir);
		$this->assertEquals(realpath($cache_dir), Cache::getCacheDir());
	}

	public function test_writeTmpFunctionWhitelist()
	{
		Cache::createTmpListFiles();
		$functions = [
			'file_exists',
			'file_get_contents',
			'file_put_contents',
		];
		Cache::writeTmpFunctionWhitelist($functions);
		
		$actual = Cache::getTmpFunctionWhitelist();
		$this->assertEquals($functions, $actual);
	}



	public function test_clearSrcCache()
	{
		Cache::clearSrcCache();
		$this->assertFalse(file_exists(
			CIPHPUnitTestReflection::getPrivateProperty(
				__NAMESPACE__.'\Cache', 'src_cache_dir'
			)
		));
	}

	public function test_clearCache()
	{
		Cache::clearCache();
		$this->assertFalse(file_exists(
			CIPHPUnitTestReflection::getPrivateProperty(
				__NAMESPACE__.'\Cache', 'cache_dir'
			)
		));
	}
}
