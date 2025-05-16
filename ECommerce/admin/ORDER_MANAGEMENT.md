# Artizo Order Management System

This document explains how to use the order management system in the Artizo admin panel.

## Order Approval Process

When a new order is placed by a customer, it is initially set to "pending" status. As an admin, you can approve these orders, which will:

1. Change the order status to "processing"
2. Send an email notification to the customer
3. Allow you to further manage the order through its lifecycle

## How to Approve Orders

### From the Dashboard

1. On the admin dashboard, you'll see a section for "Recent Orders"
2. Pending orders will have an "Approve" button next to them
3. Click the "Approve" button to open the approval confirmation modal
4. Click "Approve Order" to confirm

### From the Orders Page

1. Navigate to "Orders" in the sidebar
2. You can filter orders by status using the filter buttons at the top
3. For pending orders, click "Update Status" to open the status update modal
4. Select "Processing" from the dropdown and click "Update"

### From the Order Detail Page

1. Navigate to the detail page of any order by clicking "View" on the orders list
2. Click the "Update Status" button at the top of the page
3. Select "Processing" from the dropdown and click "Update"

## Order Status Lifecycle

Orders in Artizo follow this typical lifecycle:

1. **Pending**: Initial state when an order is placed but not yet approved
2. **Processing**: Order has been approved and is being prepared
3. **Shipped**: Order has been shipped to the customer
4. **Delivered**: Order has been successfully delivered
5. **Cancelled**: Order has been cancelled (can happen at any stage)

Each status change triggers an email notification to the customer, keeping them informed about their order's progress.

## Email Notifications

The system automatically sends email notifications to customers when:

1. An order is approved (status changes to "processing")
2. An order is shipped
3. An order is delivered
4. An order is cancelled

These emails include:
- Order number
- New status
- Additional information based on the status
- Order total
- Contact information

## Managing Order Details

On the order detail page, you can:

1. View customer information
2. View shipping and billing addresses
3. See all ordered items with prices and quantities
4. Add admin notes for internal reference
5. Update the order status

## Best Practices

1. Process pending orders promptly (within 24 hours)
2. Add detailed admin notes for any special handling or customer communications
3. Update order statuses in real-time to keep customers informed
4. Review the dashboard daily for new pending orders 