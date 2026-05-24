<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class MobileCacheTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testMobileDetectionCachedInSession(): void
    {
        $response = $this->withSession(['_mobile_cache' => null])
                         ->get('/');

        $session = service('session');
        $cached  = $session->get('_mobile_cache');

        $this->assertNotNull($cached);
        $this->assertArrayHasKey('is_mobile', $cached);
        $this->assertArrayHasKey('preview_mode', $cached);
        $this->assertArrayHasKey('timestamp', $cached);
    }

    public function testCacheReturnsCachedValue(): void
    {
        $session = service('session');
        $session->set('_mobile_cache', [
            'is_mobile'    => true,
            'preview_mode' => null,
            'timestamp'    => time(),
        ]);

        $response = $this->get('/');
        $cached   = $session->get('_mobile_cache');

        $this->assertTrue($cached['is_mobile']);
    }

    public function testCacheInvalidatedOnPreviewChange(): void
    {
        $session = service('session');
        $session->set('_mobile_cache', [
            'is_mobile'    => false,
            'preview_mode' => 'desktop',
            'timestamp'    => time(),
        ]);

        $response = $this->get('/?preview=mobile');
        $cached   = $session->get('_mobile_cache');

        $this->assertEquals('mobile', $cached['preview_mode']);
        $this->assertTrue($cached['is_mobile']);
    }

    public function testClearPreviewResetsCache(): void
    {
        $session = service('session');
        $session->set('_mobile_cache', [
            'is_mobile'    => true,
            'preview_mode' => 'mobile',
            'timestamp'    => time(),
        ]);

        $response = $this->get('/?preview=clear');
        $cached   = $session->get('_mobile_cache');

        $this->assertNull($cached);
    }

    public function testInvalidPreviewValueSanitized(): void
    {
        $session = service('session');
        $session->set('_mobile_cache', [
            'is_mobile'    => true,
            'preview_mode' => 'mobile',
            'timestamp'    => time(),
        ]);

        $response = $this->get('/?preview=invalid');
        $cached   = $session->get('_mobile_cache');

        $this->assertNotNull($cached);
        $this->assertNull($cached['preview_mode']);
    }
}
