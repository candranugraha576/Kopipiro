# Kopipiro - Coffee Shop Web Application

A full-stack PHP & MySQL ordering web application developed for **Kopipiro**. The platform supports role-based access control (Owner, Cashier, Customer, and Guest), dynamic product exploration, automated member discounts, cashier order validation, and owner-level sales analytics with Excel export capabilities.

---

## Features

### Customer & Guest Experience
- **Explore Products**: Filter by category (Coffee, Non-Coffee, Toast, Snacks) with ratings, prices, and descriptions.
- **Shopping Cart**: Session- and database-backed multi-item cart.
- **Member Discount**: Registered & logged-in customers automatically receive a **10% discount on coffee items** at checkout. Non-coffee products and guest checkouts remain at standard pricing.
- **Order History**: Customers can review their past transactions and fulfillment statuses.

### Cashier Management
- **Order Processing**: Cashiers can view incoming/unassigned orders and validate/claim orders once paid.
- **Dedicated Queue**: Cashiers manage orders assigned to their account.

### Owner Dashboard & Analytics
- **Product CRUD**: Add, edit, delete menu items, and upload item images.
- **Sales Analytics**: Interactive 7-day revenue performance chart built with Chart.js.
- **Excel Export**: Generate and download real-time order and revenue records in `.csv` format compatible with Microsoft Excel.

---

## Database Architecture

The relational schema comprises 7 primary entities:
- `roles` (Owner, Cashier, Customer)
- `users` (Role-based authentication)
- `categories` (Product classifications)
- `products` (Name, price, category, rating, image)
- `carts` (Cart line items mapped by user or session)
- `orders` (Master order records, discounts, assigned staff)[cite: 3]
- `order_details` (Line items, historical prices, and per-item discounts)[cite: 3]

---
