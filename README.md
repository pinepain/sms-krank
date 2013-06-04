sms-krank
=========
[![Build Status](https://travis-ci.org/pinepain/sms-krank.png)](https://travis-ci.org/pinepain/sms-krank)

A library for sending SMS messages through various service providers (gateways) based on pre-sets and phone number to minimize costs.
The main purpose is to reduce SMS costs by sengind messages through the most suitable (cheeper) providers based on pre-sets and/or phone number.

As you can noted (you probably did some resarch before find this lib, aren't you?) sending SMS messages internationally is quite expensive if you did it through one gateway. For example, sending message from some provider in Europe to United States will costs $0.06, when sending via provider in USA will costs just $0.01. When you are sending one or two messages you don't care, but when you sending few thousands 5 cents transforms into few hundred bucks.

The reason why you want to send thousands of sms may vary, but one of use cases I can suggest is user secure login, informing user about new items in stock (sure, if he previously ask to send such messages).

TODO
----

- [ ] unit tests
- [ ] code coverage tools (https://coveralls.io?) integration into github page
- [ ] check gates whether they are alive and credentials are valid
- [ ] handle message length over capacity


Message capacity
----------------
```
 Encoding | Single | Partial
-----------------------------
   7 bit  |  160   |  153
   8 bit  |  140   |  134
  16 bit  |   70   |   67

```