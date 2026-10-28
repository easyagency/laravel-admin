<?php

namespace Easyagency\LaravelAdmin\Tests;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\ServiceProvider as BreadcrumbsServiceProvider;
use Easyagency\LaravelAdmin\Breadcrumbs\BreadcrumbsManager;
use Easyagency\LaravelAdmin\Http\Requests\Request;
use Easyagency\LaravelAdmin\LaravelAdminServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Orchestra\Testbench\TestCase;

class AdminCompatibilityTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [BreadcrumbsServiceProvider::class, LaravelAdminServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('admin.routes.file', '/nonexistent-admin-test-routes.php');
        $app['config']->set('admin.routes.middleware', 'web');
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    }

    public function testPackageRegistersRoutesAndCustomBreadcrumbManager(): void
    {
        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue(Route::has('admin.cache.clear'));
        $this->assertInstanceOf(BreadcrumbsManager::class, app(\Diglactic\Breadcrumbs\Manager::class));
        $this->assertTrue(Breadcrumbs::exists('admin.dashboard'));
        Route::get('/admin/examples', fn () => 'examples')->name('admin.examples.index');
        Breadcrumbs::resource('admin.examples', 'Examples');
        $this->assertCount(2, Breadcrumbs::generate('admin.examples.index'));
    }

    public function testLegacyFormGroupDirectiveStillRenders(): void
    {
        view()->share('errors', new ViewErrorBag);
        $html = Blade::render(
            '@formGroup(["name" => "title", "label" => "Title"])<input name="title">@endformGroup',
            ['errors' => new ViewErrorBag],
        );
        $this->assertStringContainsString('<input name="title">', $html);
        $this->assertStringContainsString('Title:', $html);
    }

    public function testRequestDispatchesCreateAndUpdateRules(): void
    {
        $request = new class extends Request {
            public function rulesCreate(): array { return ['title' => ['required']]; }
            public function rulesUpdate(): array { return ['title' => ['sometimes']]; }
        };
        $request->setMethod('POST');
        $this->assertSame(['title' => ['required']], $request->rules());
        $request->setMethod('PATCH');
        $this->assertSame(['title' => ['sometimes']], $request->rules());
    }
}
