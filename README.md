#Lsyncd-AWS-AutoScaling

Lsyncd auto configuration that works with Amazon Web Services (AWS) Auto Scaling.

Lsyncd is Live Syncing (Mirror) Daemon.

It does the following:

1. Monitors auto scaled instances that are attached to a load balancer.
2. On master node, configures lsyncd to sync to all slave nodes
3. On slave nodes, configures lsyncd to sync a certain directory back to master node.
4. Monitors Lsyncd and make sure Lsyncd is always up and running, while Lsyncd does the syncing of files from master to auto-scaled slaves.

##Pre-requisites & assumptions

Lsyncd-AWS-AutoScaling requires the following to be set up and running on your master server:

1. [lsyncd](https://github.com/axkibe/lsyncd) is set up on all nodes.
1. Passwordless SSH is possible from master to slave via the use of a private key.

This project is tested on Lsyncd v2.1.5 and on Centos 7 instances.

##Set Up

1. Set up [Composer](http://getcomposer.org/)
1. `composer install` to install the dependencies.
1. `cp config.php.default config.php` and edit accordingly.

##Run

```bash
php monitor.php
```

That's all.

It is recommended to set it to run via crontab to ensure that it is run periodically and automatically.

##Disclaimer
This is a rough project that solves our specific problems and may not work with other set-ups. Not a lot of effort was spent in generalizing this project. Pull requests are welcomed.

##License
The MIT License