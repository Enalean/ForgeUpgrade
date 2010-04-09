Questions
=========
* What is a successfull upgrade?
** When everything went OK.

* What is an faild upgrade
** When one of the operation faild ?
** When all the operations faild ?
** Who decide to continue or skip the developer or the site admin ?

Specifications
==============
- Do not try to hit the automation bird (puppet, etc).
  The migration does what it has to do (modifying files, etc) and
  this is the autmation process that can override those file afterward
  If we could help admin to detect which files will be impacted, it
  would be great thought.

Technical
=========
- Use [log4php](http://incubator.apache.org/log4php/) for logging
