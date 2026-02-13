# Implementation Plan - Phase 5: Checkout System

The goal is to implement a functional **Guest Checkout** flow to allow users to complete purchases immediately without registering.

## Database Updates
- [MODIFY] Table `orders`: Add columns for guest checkout (`customer_name`, `email`, `phone`, `address`, `status`).
- [NEW] Table `order_items`: Stores individual products/variants for each order (`order_id`, `product_id`, `variant_id`, `quantity`, `price`).

## Frontend
- [MODIFY] `cart.php`: Update "Checkout" button to link to `checkout.php`.
- [NEW] `checkout.php`: 
    - Display Order Summary (Total).
    - Form to collect Shipping Info (Name, Address, Phone, Email).
    - Payment Method Selection (Mock: COD / Bank Transfer).

## Backend
- [NEW] `process_order.php`:
    - Validate inputs & CSRF Token.
    - Insert Order into `orders` table.
    - Insert Items into `order_items` table.
    - Clear `$_SESSION['cart']`.
    - Redirect to `success.php?order_id=XYZ`.
- [NEW] `success.php`: Display "Thank You" message and Order Details.

## Verification
- Test placing an order with multiple items/variants.
- Verify data in database (orders and order_items).
- Verify cart is cleared after purchase.
