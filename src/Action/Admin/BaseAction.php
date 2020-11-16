<?php
namespace App\Action\Admin;

use App\Domain\Blog\AdminRepository;
use Nhymxu\Responder;
use Psr\Container\ContainerInterface;

class BaseAction
{
    /**
     * @var Responder
     */
    protected $responder;

    /**
     * @var AdminRepository
     */
    protected $repository;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * PostAction constructor.
     *
     * @param Responder $responder The responder
     * @param AdminRepository $repository
     * @param ContainerInterface $container
     */
    public function __construct(Responder $responder, AdminRepository $repository, ContainerInterface $container)
    {
        $this->responder = $responder;
        $this->repository = $repository;
        $this->container = $container;
    }
}
