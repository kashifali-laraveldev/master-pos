<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="MasterPOS API",
 *     version="1.0.0",
 *     description="Tenant-aware API documentation for MasterPOS."
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Sanctum bearer token. Format: Bearer {token}"
 * )
 * @OA\Tag(name="Auth", description="Authentication and tenant registration")
 * @OA\Tag(name="Products", description="Product management endpoints")
 * @OA\Tag(name="Categories", description="Category management endpoints")
 * @OA\Tag(name="Sales", description="Sales and reports endpoints")
 * @OA\Tag(name="Customers", description="Customer management endpoints")
 * @OA\Tag(name="Inventory", description="Inventory and stock movement endpoints")
 * @OA\Tag(name="Dashboard", description="Dashboard analytics endpoints")
 * @OA\Tag(name="Users", description="Tenant user management endpoints")
 */
abstract class Controller
{
    //
}
