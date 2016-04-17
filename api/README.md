TODO
----

* Move config parameters like DB credentials to untracked config file
* Integrate LDAP logic from [credentials](https://github.com/jnwatts/credentials/blob/ba86621dfb32b225ac78ba3ff2fb89a8e8e7d1af/app/Helpers/User.php#L107)
* Need to start deciding on level of complexity for requests & their items
* Define roles
    * Admin: Access everything
    * Signatory: (Better name?) Elligable for signature field

Schema brainstorming
--------------------
All schemas implicitly have id attribute

* Request belong to User
* Request has 0+ Users (CC list) (M:M index table)
* Request has 0+ Items
* Request attributes:
    * QuoteNum
    * VendName
    * Signature0 (One for each purchase level)
    * Signature1
    * Signature2
    * ...
* Item belongs to Request
* Item attributes:
    * PartNum
    * Description
    * Quantity
    * Price
* User has 0+ Requests
* User has 0+ Roles
* User attributes:
    * Name
    * Login
    * Email
* Role attributes:
    * Name
    * Description
