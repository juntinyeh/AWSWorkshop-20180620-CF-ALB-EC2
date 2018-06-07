Startup Workshop Series (2018-06-20) Cloudfront-ApplicationLoadBalancer-EC2
======
### Repo: [https://github.com/juntinyeh/AWSWorkshop-20180620-CF-ALB-EC2]

Today we are going to several configuration detail in Amazon CloudFront, which can support us to setup the distribution in front of our web services and application servers. From our last workshop, we already discussed about how to separate the static assets from dynamic content, if you want to review more detail, please check [Here](https://github.com/juntinyeh/AWSWorkshop-20180524-EC2-S3-CF)

![AWS Workshop Series - s3originbehavior](https://raw.githubusercontent.com/juntinyeh/AWSWorkshop-20180524-EC2-S3-CF/master/images/s3originbehavior.png)

For this workshop, we support 3 different region: 
* N. Viginia(us-east-1)
* N. California(us-west-1)
* Tokyo(ap-northeast-1)
* Sydney(ap-southeast-2) 
* Frankfurt(eu-central-1)
* London(eu-west-2)

We pick these region becase later we will deploy cloudfront distribution, which can obviously see the difference after CDN enabled.
------

### Step 1:
Switch Region on the AWS console, a drag down menu near right-up corner.
* N. Viginia(us-east-1)
* N. California(us-west-1)
* Tokyo(ap-northeast-1)
* Sydney(ap-southeast-2) 
* Frankfurt(eu-central-1)
* London(eu-west-2)

### Step 2:
* Check if you already have a EC2 Key pair in your selected region. 
* If not, create one through **AWS Console > EC2 > Key Pairs > Create Key Pair**. 
* Remember to download the private key(.pem) and well saved. 
* In usual, we will move it into ~/.ssh/ sub-folder in your home directory.
* To make it secure, remeber to change the privilege with command 
``` chmod 0400 XXXXX.pem ```
* If you are windows user, and you should download putty.exe and puttygen.exe from [Here](https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html). And also check the document [Here](https://www.ssh.com/ssh/putty/windows/puttygen) if you don't know how to convert .pem to .ppk

### Step 3:
* Create your CoudFormation stack: **AWS Console > Cloudformation > Create Stack > from S3 template >
https://s3-ap-northeast-1.amazonaws.com/workshop-data-public/cloudformation-workshop-20180524-ec2-s3.json**
* Wait till the stack creation ready, the status will change to `CREATE_COMPLETE` (15-20 minutes)
* you can see the several information in "Resources" and "Output" sheet:

### Step 4:
* Check the Web Service output from ALB-
  Open the Output Sheet, and check the ALBDNSName, you will find a link like 'http://xxxxxxx-ALB.region.amazon.com'
  Click the link you will see a response page like this:
  
  Which is the output from you ALB.
  
* Check the Web Service output from CloudFront- 
  Open the Output Sheet, and check the CloudFront, you will find a link like 'http://xxxxxxx-ALB.cloudfront.net'
  Click the link you will see a response page like this:
  
  Which is the output from you ALB.

### Step 5:
#### Now, We startup play with TTL.
* Change the MinTTL/DefaultTTL/MaxTTL with different value, and flush the page on your browser, you can see the different behaviors.
* Change the MinTTL=60, DefaultTTL=300, MaxTTL=900, and then refresh the browser, and check the timestemp on the web page.
* Use curl or browser plug-in (like postman or Modheader in chrome), and play with different headers:
```
    Cache-Control: max-age=<seconds>
    Cache-Control: max-stale[=<seconds>]
    Cache-Control: min-fresh=<seconds>

    Cache-Control: no-cache 
    Cache-Control: no-store
    Cache-Control: no-transform
    Cache-Control: only-if-cached
    Cache-Control: public
    Cache-Control: private

    Cache-Control: proxy-revalidate
    Cache-Control: must-revalidate
    
```

#### 5.1 Change the TTL from server response
* Connect to your EC2 instance through SSH
* Now we need to create a ttl.php with following content:
```
<?php

      header('Cache-Control: max-age=60');

      // Print out the current data and time

      print "The Current Date and Time is: <br/>";
      print date("g:i:s A l, F j Y.");
?>
```
And please through your browser [http://YOUR_CF_DISTRIBUTION.cloudfront.net/ttl.php] for the observation.
Modify the max-age setting in your source code, and see the behavior change on your browser. 
* Change the code ```header('Cache-Control: max-age=10'); ```
* Change the code ```header('Cache-Control: max-age=600'); ```
* Combind with the browser developer tool with 'no-cache' box check.
* change the Minial-TTL of your origin behavior to 300, and test the behaviors

#### 5.2
* Now we create another page nocache.php:
<?php

      header('Cache-Control: public, no-cache, no-store, must-revalidate, proxy-revalidate');

      // Print out the current data and time

      print "The Current Date and Time is: <br/>";
      print date("g:i:s A l, F j Y.");
?>
* And create another behavior set path as "/nocache.php", with all the TTL setting to 300, and check with the behavior [http://YOUR_CF_DISTRIBUTION.cloudfront.net/nocache.php] .

After this, please check with the behavior 

For more detail and description, please refer to [https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Expiration.html]
[https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Expiration.html#ExpirationDownloadDist]
And also the TTL section in our official document:
[https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/distribution-web-values-specify.html#DownloadDistValuesMinTTL]
And then we can understand the setting combination between server response header and distribution settings.

### Step 6:
* Check with the behavior, how do we handle the request header in the requests?

```
aws cloudfront create-invalidation --distribution-id "XXXXXXXXXXXX" --paths "/nocache.php"
```
```
{
    "Invalidation": {
        "Status": "InProgress", 
        "InvalidationBatch": {
            "Paths": {
                "Items": [
                    "/nocache.php"
                ], 
                "Quantity": 1
            }, 
            "CallerReference": "cli-1234567890-12345678"
        }, 
        "Id": "ICV0AYDA406NC", 
        "CreateTime": "2018-06-07T04:28:01.364Z"
    }, 
    "Location": "https://cloudfront.amazonaws.com/2017-03-25/distribution/XXXXXXXXXXX/invalidation/XXXXXXXXXXXX"
}

```

### Step 7:
### Call the request with different queryString
* Set the "bgcolor" into the queyrString configuration column
  And try with following link [http://YOUR_CF_DISTRIBUTION.cloudfront.net/index.php?bgcolor=red]
  And also with following link [http://YOUR_CF_DISTRIBUTION.cloudfront.net/index.php?bgcolor=red&ftcolor=white]
  In this case you can see how we can control the valid query string filter for your CloudFront distribution.
  
From our official document [https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/QueryStringParameters.html], you can find that

======
## Till here, you already know several detail about setting a CloudFront distribution in front of your dynamic page and API server. 
### What's next?
You might already awared about there is still a easy way to get into your server without HTTPS, right? The ALB Domain is there and the Security Group is still open to public. How should I fix it now? 
Please check the page [https://aws.amazon.com/blogs/security/how-to-automatically-update-your-security-groups-for-amazon-cloudfront-and-aws-waf-by-using-aws-lambda/], which teach you to build up an automation flow to secure your ALB.

## After Workshop
1. Go to Cloudformation and delete stack.
