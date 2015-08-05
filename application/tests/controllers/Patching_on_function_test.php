<?php

/**
 * @group patcher
 */
class Patching_on_function_test extends TestCase
{
	public function test_index_patch_on_mt_rand()
	{
		MonkeyPatch::patchFunction('mt_rand', 100);
		$output = $this->request('GET', 'patching_on_function');
		$this->assertContains('100', $output);
	}

	public function test_another_patch_on_mt_rand()
	{
		MonkeyPatch::patchFunction('mt_rand', function($a, $b) {
			return $a . $b;
		});
		$output = $this->request('GET', 'patching_on_function/another');
		$this->assertContains('19', $output);
	}

	public function test_openssl_random_pseudo_bytes()
	{
		MonkeyPatch::patchFunction('openssl_random_pseudo_bytes', 'aaaa');
		$output = $this->request(
			'GET', 'patching_on_function/openssl_random_pseudo_bytes'
		);
		$this->assertEquals("61616161\n1\n", $output);
	}

	public function test_openssl_random_pseudo_bytes_callable()
	{
		MonkeyPatch::patchFunction(
			'openssl_random_pseudo_bytes',
			function ($int, &$crypto_strong) {
				$crypto_strong = false;
				return 'bbbb';
			}
		);
		$output = $this->request(
			'GET', 'patching_on_function/openssl_random_pseudo_bytes'
		);
		$this->assertEquals("62626262\n\n", $output);
	}

	public function test_function_exists_use_random_bytes()
	{
		MonkeyPatch::patchFunction(
			'function_exists',
			function ($function) {
				if ($function === 'random_bytes')
				{
					return true;
				}
				elseif ($function === 'openssl_random_pseudo_bytes')
				{
					return false;
				}
				elseif ($function === 'mcrypt_create_iv')
				{
					return false;
				}
				else
				{
					return __GO_TO_ORIG__;
				}
			}
		);
		$output = $this->request(
			'GET', 'patching_on_function/function_exists'
		);
		$this->assertEquals("I use random_bytes().", $output);
	}

	public function test_function_exists_use_openssl_random_pseudo_bytes()
	{
		MonkeyPatch::patchFunction(
			'function_exists',
			function ($function) {
				if ($function === 'random_bytes')
				{
					return false;
				}
				elseif ($function === 'openssl_random_pseudo_bytes')
				{
					return true;
				}
				elseif ($function === 'mcrypt_create_iv')
				{
					return false;
				}
				else
				{
					return __GO_TO_ORIG__;
				}
			}
		);
		$output = $this->request(
			'GET', 'patching_on_function/function_exists'
		);
		$this->assertEquals("I use openssl_random_pseudo_bytes().", $output);
	}

	public function test_function_exists_use_mcrypt_create_iv()
	{
		MonkeyPatch::patchFunction(
			'function_exists',
			function ($function) {
				if ($function === 'random_bytes')
				{
					return false;
				}
				elseif ($function === 'openssl_random_pseudo_bytes')
				{
					return false;
				}
				elseif ($function === 'mcrypt_create_iv')
				{
					return true;
				}
				else
				{
					return __GO_TO_ORIG__;
				}
			}
		);
		$output = $this->request(
			'GET', 'patching_on_function/function_exists'
		);
		$this->assertEquals("I use mcrypt_create_iv().", $output);
	}
}
