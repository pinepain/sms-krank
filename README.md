WARNING: This project is UNSUPPORTED and ABANDONED
==================================================



sms-krank
=========
[![Build Status](https://travis-ci.org/pinepain/sms-krank.png)](https://travis-ci.org/pinepain/sms-krank)

**The library is under active development so API may be changed without any notice**

A library for sending SMS messages through various service providers (gateways) based on pre-sets and phone number to minimize costs.
The main purpose is to reduce SMS costs by sendind messages through the most suitable (cheaper) providers based on pre-sets and/or phone number.

As you can noted (you probably did some research before find this lib, aren't you?) sending SMS messages internationally is quite expensive if you did it through one gateway. For example, sending message from some provider in Europe to United States will costs $0.06, when sending via provider in USA will costs just $0.01. When you are sending one or two messages you don't care, but when you sending few thousands 5 cents transforms into few hundred bucks.

The reason why you want to send thousands of sms may vary, but one of use cases I can suggest is user secure login, informing user about new items in stock (sure, if he previously ask to send such messages).


#### Features

* Pick up the most suitable gate based on phone number
* Message templating
* Keep text size to fit required number of messages (by default 1)
* Distinguish pure GSM encoded messages from Unicode messages to properly calculate max allowed text size.
* Remove multiple whitespace characters (optional, by default is on), cleanup broken unicode characters.
* No external dependencies (only `mb_string` extension used);

## Further reading

* [Wikipedia article about GSM 03.38] (http://en.wikipedia.org/wiki/GSM_03.38)
* [3GPP TS 23.038: Alphabets and language-specific information] (http://www.3gpp.org/ftp/Specs/html-info/23038.htm)

TODO
----

- [ ] unit tests
- [ ] code coverage tools (https://coveralls.io?) integration into github page
- [ ] check gates whether they are alive and credentials are valid

Message capacity
----------------
```
 Encoding | Single | Partial
-----------------------------
   7 bit  |  160   |  153
   8 bit  |  140   |  134
  16 bit  |   70   |   67

```
