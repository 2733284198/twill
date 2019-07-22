<?php

namespace A17\Twill\Http\Controllers\Admin;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\ResponseFactory;
use Illuminate\View\Factory as ViewFactory;

class TemplatesController extends Controller
{
    /**
     * @var ViewFactory
     */
    protected $viewFactory;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param Application $app
     * @param Config $config
     * @param ViewFactory $viewFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Application $app,
        Config $config,
        ViewFactory $viewFactory,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($app, $config);

        $this->viewFactory = $viewFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->viewFactory->make('templates.index');
    }

    /**
     * @param string $view
     * @return \Illuminate\View\View
     */
    public function view($view)
    {
        return $this->viewFactory->make('templates.' . $view);
    }

    /**
     * @param string view
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function xhr($view)
    {
        $response = [
            'data' => $this->viewFactory->make('templates.' . $view)->render(),
            'has_more' => (rand(0, 10) > 5),
        ];
        return $this->responseFactory->json($response);
    }
}
