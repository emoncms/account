# account
Multi account administration

A simple multi user management module for emoncms that allows a single user (any user) to become an admin user for a number of other linked user accounts. 

1. Provides convenient access to the linked accounts
2. New user level access control option that can be used to disable login access for a user or restrict login access as read-only.

Core goal, to keep the implementation as simple as possible, I've been round in circles enough times with group based multi user management approaches. This accounts table also mirrors the same multi user billing implementation used on emoncms.org with the intention of integrating this as an extension of that functionality so that it's possible to manage in addition to just handling the billing for linked users...

**Heatpump Monitoring**
This tool could be used by an installer to create an account for each site/installation. Providing a convenient login and list of sites/installations under management. Read only access with username and password login can be provided to users. Linked users cannot access data from other users (more secure than placing multiple sites under a single emoncms account) 
