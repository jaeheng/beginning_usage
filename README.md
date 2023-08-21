# beginning_usage
emlog plugin: Statistics and collection of user information for original applications

## feature
Pass the website name and address to the server by setting the logo image address in the template/plugin backend. Collect website names and addresses to count the number of users using our original applications.

## implementation
Modify the access path of the logo image address through nginx to appear less dangerous.

```conf
location /logo.png {
    proxy_pass https://blog.phpat.com/?plugin=beginning_usage;
}
```

This can be achieved through` https://blog.phpat.com/logo.png `To access the logo image, with GET parameters:

```
url=site address
blogname=site name
type=Application identification
```

## statement
This plugin is only used for user statistics
