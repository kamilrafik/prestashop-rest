<?php

require_once dirname(__FILE__) . '/../classes/RESTTrait.php';
require_once dirname(__FILE__) . '/../classes/AuthTrait.php';

abstract class AbstractCartRESTController extends CartControllerCore
{
    use RESTTrait;
    use AuthTrait;

    public function init()
    {
        header('Content-Type: ' . "application/json");
        $this->performAuthenticationViaHeaders();
        if (!$this->context->customer->isLogged()) {
            $this->ajaxRender(json_encode([
                'code' => 410,
                'success' => false,
                'message' => $this->trans('User Not Authenticated', [], 'Modules.Binshopsrest.Admin')
            ]));
            die;
        }

        parent::init();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->processGetRequest();
                break;
            case 'POST':
                $this->processPostRequest();
                break;
            case 'PATCH':
            case 'PUT':
                $this->processPutRequest();
                break;
            case 'DELETE':
                $this->processDeleteRequest();
                break;
            default:
                // throw some error or whatever
        }
    }

    protected function checkCartProductsMinimalQuantities()
    {
        $productList = $this->context->cart->getProducts();

        foreach ($productList as $product) {
            if ($product['minimal_quantity'] > $product['cart_quantity']) {
                // display minimal quantity warning error message
                $this->errors[] = $this->trans(
                    'The minimum purchase order quantity for the product %product% is %quantity%.',
                    [
                        '%product%' => $product['name'],
                        '%quantity%' => $product['minimal_quantity'],
                    ],
                    'Shop.Notifications.Error'
                );
            }
        }
    }
}
