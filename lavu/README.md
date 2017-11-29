# Lavu Functions

This code is intended to pull data out of the Lavu POS system using Lavu's API and then load it into a local MySQL database, where it can then be processed and analyzed. The code was designed to work for a specific site, so it's not very generalized.

The code that contacts the Lavu interface and wrangles the results was heavily copied from Troy Kelly's work presented in [this thread on the Lavu forums](http://talk.lavu.com/ipad-pos-topic/cashier-sales-style-report-with-php-393/#comment-1952).

# Usage

This is designed to be run from the command line and store the resulting data in a local MySQL database. So that's a couple of prerequisites. Once the dB is up and running, you need to do the following preparation steps:

1. Run the `create_tables.sql` code to create the necessary tables in the dB
2. Create `config.ini` and `lavu_config.ini` files. In a nutshell, `config.ini` contains the parameters needed to connect to your database and `lavu_config.ini` contains the connection strings for your Lavu account

Once those tasks are done you can load your tables by running these files. **Each one is designed as a kill-and-fill operation, so it will delete the current contents of each table before re-loading it.**

- `load_gci.php` - Loads the `menu_groups`, `menu_categories`, and `menu_items` tables
- `load_orders.php` - Loads the orders table five days at a time.
- `load_order_contents.php` - Loads the order_contents table, 1000 rows at a time.
