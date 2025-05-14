# EveryWidget #

A submodule of [EveryDataStore ECM](https://everydatastore.org) that provides the EveryWidget app. 
It allows users to customize the Dashboard by reordering, toggling, or creating widgets to fit specific group needs.

## Requirements ##
[EveryDataStoreBackend](https://github.com/EveryDataStore/EveryDataStoreBackend)<br/>


## Installation ##
- Go to EveryDataStoreBackend project directory
- Add EveryWidget as a submodule "**git submodule add https://github.com/EveryDataStore/EveryWidget**"
- Run the following URL in your browser: "**https://your-backend-url/dev/build/?flush=all**"
- Create your widgets in the frontend under the following path: “**Administration / EveryWidget**”.
- Create the following cron job in your crontab
  "** `*/5 * * * *` php {project-path}/vendor/silverstripe/framework/cli-script.php dev/tasks/EveryDataStoreTask action=createWidgetJsonFile **"<br/>
  <br/>
  ** Or ** run following URL in your browser: "**https://your-backend-url/dev/tasks/EveryDataStoreTask?action=createWidgetJsonFile**"
  
  
## Links ##
[EveryWidget Documentation](https://ceverydatastore.org/en/apps/everywidget)<br/> 
[EveryDataStore ECM](https://github.com/EveryDataStore/EveryDataStoreECM)<br/> 
[EveryDataStore Frontend](https://github.com/EveryDataStore/EveryDataStoreFrontend)<br/>
[EveryNotifyTemplate](https://github.com/EveryDataStore/EveryNotifyTemplate)<br/> 
[SilverStripe i18n](https://docs.silverstripe.org/en/5/developer_guides/i18n/)<br/> 



.