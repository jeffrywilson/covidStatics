## Install

Run this commands:

- <b><i>composer update</i></b>
- <b><i>npm install</i></b>

## Run application for local development

- <b><i>npm run watch</i></b> (watcher for update public/js/app.js)
- <b><i>php artisan serve</i></b>  (start laravel local server)


## Run Laravel Scheduler


Theoretically, there are two ways to run scheduler command:

The first method requires you to run the following command, and it’s a manual process.
- <b><i>php artisan schedule:work</i></b> <br/>
On successful execution of command you will receive the following output: <br/>
  `Running scheduled command: "D:\_Files\xampp\php\php.exe" "artisan" quote:daily > "NUL" 2>&1</i> <br/>`

In the second method, we automate the task scheduler.
To auto-starting Laravel Scheduler, we require to set Cron Job that executes after every minute.
ssh into your server, get inside your project with cd laravel-project-name and run the following command

- <b><i>crontab -e</i></b> <br/>
It will open the Crontab file, and you need to assimilate the following code in the same file.


- <b><i>* * * * * cd /your-project-path && php artisan schedule:run >> /dev/null 2>&1</i></b> <br/>
Don’t forget to replace /path/to/artisan with the full path to the custom Artisan command of the Laravel project. <br/>


