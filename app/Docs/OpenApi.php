<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'MasterPOS API', version: '1.0.0')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'Token')]
class OpenApi
{
    #[OA\Post(
        path: '/api/auth/register',
        tags: ['Auth'],
        summary: 'Tenant registration',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'business_name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ali Raza'),
                    new OA\Property(property: 'business_name', type: 'string', example: 'Demo Store'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'owner@masterpos.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'demo1234'),
                    new OA\Property(property: 'plan', type: 'string', example: 'free'),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Created'), new OA\Response(response: 422, description: 'Validation error')]
    )]
    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: 'Login',
        parameters: [
            new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant'), example: 'demo-tenant'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', default: 'demo@masterpos.com', example: 'demo@masterpos.com'),
                    new OA\Property(property: 'password', type: 'string', default: 'demo1234', example: 'demo1234'),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error')]
    )]
    #[OA\Post(path: '/api/auth/logout', tags: ['Auth'], summary: 'Logout', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/auth/me', tags: ['Auth'], summary: 'Profile', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function auth(): void {}

    #[OA\Get(path: '/api/products', tags: ['Products'], summary: 'List products', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant'))], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Post(path: '/api/products', tags: ['Products'], summary: 'Create product', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [new OA\Property(property: 'category_id', type: 'integer', default: 1), new OA\Property(property: 'name', type: 'string', default: 'Basmati Chawal 1kg'), new OA\Property(property: 'sku', type: 'string', default: 'KR-CH-1001'), new OA\Property(property: 'unit_type', type: 'string', default: 'piece'), new OA\Property(property: 'unit_label', type: 'string', default: 'pack'), new OA\Property(property: 'stock_unit', type: 'string', default: 'pack'), new OA\Property(property: 'price_per_unit', type: 'number', default: 420), new OA\Property(property: 'cost_price', type: 'number', default: 360), new OA\Property(property: 'stock_quantity', type: 'number', default: 50), new OA\Property(property: 'low_stock_alert', type: 'integer', default: 8)])), security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/products/{id}', tags: ['Products'], summary: 'Get product', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant')), new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', default: 1))], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Put(path: '/api/products/{id}', tags: ['Products'], summary: 'Update product', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant')), new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', default: 1))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [new OA\Property(property: 'price_per_unit', type: 'number', default: 450), new OA\Property(property: 'low_stock_alert', type: 'integer', default: 10)])), security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Delete(path: '/api/products/{id}', tags: ['Products'], summary: 'Delete product', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant')), new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', default: 1))], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/products/low-stock', tags: ['Products'], summary: 'Low stock products', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function products(): void {}

    #[OA\Get(path: '/api/categories', tags: ['Categories'], summary: 'List categories', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant'))], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Post(path: '/api/categories', tags: ['Categories'], summary: 'Create category', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [new OA\Property(property: 'name', type: 'string', default: 'Atta & Flour'), new OA\Property(property: 'description', type: 'string', default: 'Gandum atta aur flour items'), new OA\Property(property: 'color', type: 'string', default: '#f59e0b')])), security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Put(path: '/api/categories/{id}', tags: ['Categories'], summary: 'Update category', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant')), new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', default: 1))], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Delete(path: '/api/categories/{id}', tags: ['Categories'], summary: 'Delete category', parameters: [new OA\Parameter(name: 'X-Tenant-Id', in: 'header', required: true, schema: new OA\Schema(type: 'string', default: 'demo-tenant')), new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', default: 1))], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function categories(): void {}

    #[OA\Post(path: '/api/sales', tags: ['Sales'], summary: 'Create sale', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/sales', tags: ['Sales'], summary: 'List sales', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/sales/{id}', tags: ['Sales'], summary: 'Get sale', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/sales/daily-report', tags: ['Sales'], summary: 'Daily report', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/sales/monthly-report', tags: ['Sales'], summary: 'Monthly report', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function sales(): void {}

    #[OA\Get(path: '/api/customers', tags: ['Customers'], summary: 'List customers', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Post(path: '/api/customers', tags: ['Customers'], summary: 'Create customer', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Put(path: '/api/customers/{id}', tags: ['Customers'], summary: 'Update customer', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Delete(path: '/api/customers/{id}', tags: ['Customers'], summary: 'Delete customer', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/customers/{id}/purchase-history', tags: ['Customers'], summary: 'Purchase history', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function customers(): void {}

    #[OA\Get(path: '/api/inventory', tags: ['Inventory'], summary: 'Inventory list', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Post(path: '/api/inventory/adjust', tags: ['Inventory'], summary: 'Adjust stock', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/inventory/history', tags: ['Inventory'], summary: 'Inventory history', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function inventory(): void {}

    #[OA\Get(path: '/api/dashboard/stats', tags: ['Dashboard'], summary: 'Dashboard stats', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Get(path: '/api/users', tags: ['Users'], summary: 'List users', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Post(path: '/api/users', tags: ['Users'], summary: 'Create user', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Put(path: '/api/users/{id}/role', tags: ['Users'], summary: 'Update user role', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    #[OA\Delete(path: '/api/users/{id}', tags: ['Users'], summary: 'Delete user', security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Success'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden'), new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 404, description: 'Not found')])]
    public function dashboardUsers(): void {}
}



