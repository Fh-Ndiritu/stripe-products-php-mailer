# stripe-products-php-mailer
A simple project to email digital products to customers who purchase them using the Stripe webhook in PHP, cURL and PHPMailer library

## Requirements
PHP 7.0 or higher
A Stripe account - you will need Stripe API KEYS
A SMTP server (e.g. Sendgrid, Mailgun)

## Installation
Clone the repository: git clone https://github.com/Fh-Ndiritu/stripe-products-php-mailer.git
Navigate to the project directory: cd stripe-products-php-mailer
Install dependencies: composer install and add <a href = 'https://github.com/PHPMailer/PHPMailer'> PHPMailer </a> Library via composer or download the whole repo
Navigate to Stripe Dashboard and copy the API Keys
Setup a stripe webhook to listen to the checkout_session.completed event
Add the list of products to send over into the stripe_products table in your Database with including stripe_name, product_name and price_id as stripe_id

## Dependencies

<a href = 'https://github.com/PHPMailer/PHPMailer'> PHPMailer </a>

## LICENSE

See the License file

## Improvements

Hosting the list of sendable products in Google sheet instead of DB for easy maintenance -- Coming soon :)
