# BunnyCDN WHMCS Module
This provisioning module for WHMCS can be used to create new pull zones inside bunnycdn through WHMCS. However, this module currently does not support features like Purge specific URL, changing other settings etc.

Only Create/Suspend/Unsuspend/Terminate/Purge All Cache actions are available. But if you are interested in contributing, please open a PR.


## Installation
Goto modules/servers and create a new directory named 'bunnycdn' and copy the contents of this repo there.

Or

Goto modules/servers and run below command if available
``
git clone https://github.com/jetchirag/bunnycdn-whmcs/ bunnycdn
``

### Create new server
Add a new server and select BunnyCDN in Module. Use your API key as server's password.

## While creating a product, it is important to provide a custom field option which looks like this:

![BunnyCDN-WHMCS-SETUP](https://i.imgur.com/L4z79OU.png)

API Wrapper Used: https://github.com/codewithmark/bunnycdn/
