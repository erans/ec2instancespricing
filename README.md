ec2instancespricing.py
======================

Written by Eran Sandler (@erans)    
http://eran.sandler.co.il    
http://forecastcloudy.net (@forecastcloudy)

ec2instancespricing.py is a quick & dirty library and a command line interface (CLI)
to get a list of all Amazon Web Services EC2 instances pricing including On-Demand
and Reserved instances (in all utilization levels).

The data is based on a set of JSON files used in the EC2 page (http://aws.amazon.com/ec2).
You can get a list of all available JSON files in this blog post:
http://forecastcloudy.net/2012/04/02/amazon-web-services-aws-ec2-pricing-data/

The original JSON files as published by Amazon don't contain the same values used throughout 
the EC2 API. This library/cli maps the values in the JSON files to their corresponding values
used throughout the EC2 API. Such values include region names, instance type, etc.

Data can be filtered by region, instance_type and os_type.

Importing this file will allow you to use two functions:
get_ec2_ondemand_instances_prices - to get the pricing of On-Demand instances
get_ec2_reserved_instaces_prices - to get the pricing of reserved instances (in all utilization levels)

Running this file will activate its CLI interface in which you can get output to your console 
in a CSV, JSON and table formats (default is table).

To run the command line interface, you need to install:    
argparse     - if you are running Python < 2.7    
prettytable  - to get a nice table output to your console

Both of these libraries can be installed using the 'pip install' command.

ec2instancespricing.php
======================

Adapted by Sathvik P ([@sathvikp](https://github.com/sathvikp)) for Doers' Guild ([@DoersGuild](https://github.com/DoersGuild))   
http://www.doersguild.com

This contains a class named `EC2InstancePrices` which exposes the following methods:
 - `get_ec2_ondemand_instances_prices` - to get the pricing of On-Demand instances
 - `get_ec2_reserved_instaces_prices` - to get the pricing of reserved instances (in all utilization levels)
 - `get_ec2_data` - to print out both the on-demand and reserved instance data as `JSON`
 
It currently doesn't have a Command-Line-Interface
