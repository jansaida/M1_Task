# M1_Task
Goldenscent Partner Invoices Module

Module Working Steps:

I am preassuming that the referred customers will visit the site with the below link, where we can find the customer email and referred partner.

Referred URL : http://192.168.56.1/magento/index.php/partnerinvoices/?customer_email=sk.saida62@gmail.com&partner_name=goldenscent

So here I have created one controller to store the cookie value with respect to the customer, as soon as the customer visits the site from the above URL I am storing the partner name in the cookie with customer name which will expire automatically after 24hrs.

Whenever the customer is placing the order, with help of event(sales_order_place_after) I am checking in the cookie whether the customer is referred by any partner or not, if yes fetching the partner value from cookie and saving into order table and also checking the count of ordered items and as per requirement performing invoice and shipment transactions.

Here irrespective of payment methods simply doing offline capture for invoices.

After placing the order we can able to see the partner name in admin order grid.