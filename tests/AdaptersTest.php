<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\Route;
use Gitter\ClientAdapter\SyncAdapter;
use Gitter\ClientAdapter\AsyncAdapter;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Class AdaptersTest
 * @package Gitter\Tests
 */
class AdaptersTest extends \PHPUnit_Framework_TestCase
{
    use UnitSupport;

    /**
     * @return Route
     */
    private function route()
    {
        return Route::get('user')->toApi();
    }

    public function testSyncAdapter()
    {
        $response = $this->client()->through(SyncAdapter::class)->request($this->route());

        $this->assertInternalType('array', $response);
    }


    public function testAsyncAdapter()
    {
        /** @var PromiseInterface $promise */
        $promise = $this->client()->through(AsyncAdapter::class)->request($this->route());

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $promise
            ->then(function($response) {
                $this->assertInternalType('array', $response);
            })
            ->otherwise(function(\Throwable $e) {
                $this->throwException($e);
            });

        $promise->wait();
    }
}