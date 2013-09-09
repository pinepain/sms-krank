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
* No external dependencies (only `mb_string` extension used).
* Phone Word support (limited, see TODO)

#### Supported charsets

* GSM - [GSM 03.38] (http://en.wikipedia.org/wiki/GSM_03.38) charset (basic and extended table)
* GSCII - charset made from intersection GSM and [ASCII] (http://en.wikipedia.org/wiki/ASCII)
* Unicode - [UTF-8] (http://en.wikipedia.org/wiki/Unicode) charset

## Further reading

* [Wikipedia article about GSM 03.38] (http://en.wikipedia.org/wiki/GSM_03.38)
* [3GPP TS 23.038: Alphabets and language-specific information] (http://www.3gpp.org/ftp/Specs/html-info/23038.htm)

TODO
----

- [ ] unit tests
- [ ] check gates whether they are alive and credentials are valid
- [ ] gates testing tool (framework)
- [ ] one file loader and multiple parsers for different content types
- [ ] loaders caching
- [ ] setting context to parse local phone numbers (for example, set context NANP and deal with 234-235-5678 like with +1-234-235-5678 or same as 001-234-235-5678, it also enables International Call Prefix support
- [ ] Deal with extra characters in phone words numbers like 1-800-MY-IPHONE in fact is just 1-800-MY-IPHON (without E letter)

Message capacity
----------------
```
 Encoding | Single | Partial
-----------------------------
   7 bit  |  160   |  153
   8 bit  |  140   |  134
  16 bit  |   70   |   67

```