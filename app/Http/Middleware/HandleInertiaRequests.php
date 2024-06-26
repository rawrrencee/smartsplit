<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'flash' => [
                'show' => fn () => $request->session()->get('show'),
                'type' => fn () => $request->session()->get('type'),
                'status' => fn () => $request->session()->get('status'),
                'message' => fn () => $request->session()->get('message'),
                'description' => fn () => $request->session()->get('description'),
                'route' => fn () => $request->session()->get('route'),
                'id' => fn () => $request->session()->get('id'),
            ],
        ]);
    }
}
