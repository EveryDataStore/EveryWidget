<?php

namespace EveryWidget\Helper;

use EveryDataStore\Helper\EveryDataStoreHelper;
use EveryDataStore\Model\RecordSet\RecordSet;
use EveryDataStore\Model\RecordSet\RecordSetItem;
use EveryDataStore\Model\RecordSet\RecordSetItemData;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Assets\File;


/** EveryDataStore/EveryWidget v1.0
 * 
 * This class defines different types of widgets as well as methods for filling them in with data
 * 
 */

class EveryWidgetHelper extends EveryDataStoreHelper {

    /**
     * This function returns Model data for the modelGrid widget
     * configuration
     * widget_modelgrid_name_{modelname}
     * @param DataObject $widget
     * @return type
     */
    public static function modelGridData($widget) {
        $conf =  self::getWidgetConfig($widget->Configurations(), 'widget_modelgrid_');
        if(!$conf) return;
        $title = isset($conf['title']) ? $conf['title']: null;
        $name = isset($conf['name']) ? $conf['name']: null;
        $filter =  isset($conf['filter']) ? $conf['filter']: [];
        $limit = isset($conf['limit']) ? $conf['limit']: 10;
        $sortcolumn =  isset($conf['sortcolumn']) ? $conf['sortcolumn']: null;
        $sortdir =   isset($conf['sortdir']) ? $conf['sortdir']: null;
        $className = $name ? Config::inst()->get('API_Namespace_Class_Map', $name) : null;
        $labels = $className ? Config::inst()->get($className, 'API_View_Fields') : null;
        $sort = $sortcolumn . ' ' . $sortdir;
       
        $DataStoreIDMap = Config::inst()->get($className, 'API_Filter_DataStoreID_Map');
        if ($DataStoreIDMap && $DataStoreIDMap !== 'false') {
            $filter[$DataStoreIDMap] = self::getCurrentDataStoreID();
        }
        
     
        $items = $className && $labels ? DataObject::get($className)->filter($filter)->sort($sort)->limit($limit) : null;
        $niceItems = [];

        foreach ($items as $item) {
            $niceItems[] = \EveryRESTfulAPI\Helper\EveryRESTfulAPIHelper::getViewFieldsValues($item);
        }
        
        return array(
            'Labels' => $labels,
            'Items' => $niceItems,
            'Name' => $title,
            'Slug' => $name,
        );
       
    }

    /**
     * This function returns Record data for the recordGrid widget
     * @Configuration widget_recordgrid_{RecordSetName}
     * @param DataObject $widget
     * @return array
     */
    public static function recordGridData($widget) {
        $conf =  self::getWidgetConfig($widget->Configurations(), 'widget_recordsetgrid_');
        if(!$conf) return;
        $title = isset($conf['title']) ? $conf['title']: null;
        $slug = isset($conf['slug']) ? $conf['slug']: null;
        $fields = isset($conf['fields']) ? $conf['fields']: null;
        $filter = isset($conf['filter']) ? $conf['filter']: null;
        $limit = isset($conf['limit']) ? $conf['limit']: null;
        $sortcolumn =  isset($conf['sortcolumn']) ? $conf['sortcolumn']: 'Created';
        $sortdir =   isset($conf['sortdir']) ? $conf['sortdir']: 'DESC';
        return self::getRecordData($slug, $fields, [], $filter, $limit, $sortcolumn, $sortdir);
    }
    
  
    /**
     * Planned for the release EveryDataStore v1.5
     * This function returns list of open tasks within workflow 
     * @configuration widget_taskgrid
     * @param DataObject $widget
     * @return array
     
    public static function taskGridData($widget) {
        if(ClassInfo::exists('EveryWorkflow\EveryRESTfulAPI\Custom\EveryWorkflowCustomFnc')){
            $tasks = \EveryWorkflow\EveryRESTfulAPI\Custom\EveryWorkflowCustomFnc::getTasks();
            if($tasks){
                $ret = [];
                foreach($tasks as $task){
                   $ret[] = [
                       'Slug' => $task->Slug,
                       'Created' => self::getNiceDateTimeFormat($task->Created),
                       'RecordSetItemSlug' => $task->RecordSetItem()->Slug,
                       'Action' => $task->Action()->Title,
                       'Definition' => $task->Action()->EveryWorkflowDefinition()->Title,
                       'DefinitionSlug' => $task->Action()->EveryWorkflowDefinition()->Slug
                       ];
                }
               return $ret; 
            }
        }
    }
    */


    /**
     * This function returns RecordSetItem data for the FeaturedInfoBox widget
     * widget_featuredInfoBoxData_RecordSetItemSlug
     * @param DataObject $widget
     * @return array
     */
    public static function featuredInfoBoxData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_featuredInfo');
        if(!$conf) return;
        if ($conf) {
            $item_slug = $conf['item_slug'];
            $field_slug = $conf['field_slug'];
            $recordSetItem = RecordSetItem::get()->filter(['Slug' => $item_slug])->first();
            if ($recordSetItem) {
                $recordSetItemData = RecordSetItemData::get()->filter(['RecordSetItem.Slug' => $recordSetItem->Slug, 'FormField.Slug' =>  $field_slug])->first();
                return array(
                    'Title' => $recordSetItemData->Value(),
                    'Slug' => $recordSetItem->Slug
                );
            }
        }
    }

    /**
     * This function sets the appearance of the widget and sets values for Model counts 
     * Configurations should starting with:
     * Modelname: widget_modelcount_name
     * Filter: widget_modelcount_filter
     * Backgroundcolor: widget_modelcount_backgroundcolor
     * prefix: widget_modelcount_fields_prefix
     * suffix: widget_modelcount_fields_suffix
     * count_option: widget_modelcount_count_option
     * @param DataObject $widget
     * @return array
     */
    public static function modelCountData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_modelcount_');
        if(!$conf) return;
        $name = isset($conf['name']) ? $conf['name'] : null;
        $filter = isset($conf['filter']) ? $conf['filter'] : null;
        $fields = isset($conf['fields']) ? $conf['fields'] : null;
        $backgroundcolor = isset($conf['backgroundcolor']) ? $conf['backgroundcolor'] : null;
        $suffix = isset($conf['suffix']) ? $conf['suffix'] : null;
        $prefix = isset($conf['prefix']) ? $conf['prefix'] : null;
        $countOption = isset($conf['count_option']) ? $conf['count_option'] : null;
        $className = $name ? Config::inst()->get('API_Namespace_Class_Map', $name) : null;
        $option = $countOption == 'count' ? 'count' : 'sum';
        $DataStoreIDMap = Config::inst()->get($className, 'API_Filter_DataStoreID_Map');

        if ($DataStoreIDMap !== 'false') {
            $filter[$DataStoreIDMap] = self::getCurrentDataStoreID();
        }

        $count = 0;
        if ($option == 'count' && $fields == '*') {
            $count = DataObject::get($className)->filter($filter)->Count();
        } else {
            $objects = DataObject::get($className)->filter($filter);
            $count = 0;
            foreach ($objects as $obj) {
                $count += (int) $obj->{$field};
            }
        }

        return array(
            'count' => $count > 1000 ? $prefix . number_format($count, 2, ',', '.') . $suffix: $prefix .$count. $suffix,
            'backgroundcolor' => $backgroundcolor
        );
    }

    /**
     * This function sets the appearance of the widget and sets values for Record counts 
     * recordSlug: widget_recordcount_slug
     * filter:widget_recordcount_filter
     * formfield_slug: widget_recordsetcount_formfield_slug
     * backgroundcolor: widget_recordcount_backgroundcolor
     * sufixx: widget_recordcount_suffix
     * prefix: widget_recordcount_prefix
     * countOption: widget_recordcount_count_option
     * @param DataObject $widget
     * @return array
     */
    public static function recordCountData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_recordsetcount_');
        if(!$conf) return;
        $recordSetSlug = isset($conf['slug']) ? $conf['slug'] : null;
        $filter = isset($conf['filter']) ? $conf['filter'] : null;
        $fields = isset($conf['fields']) ? $conf['fields'] : null;
        $backgroundcolor = isset($conf['backgroundcolor']) ? $conf['backgroundcolor'] : null;
        $suffix = isset($conf['suffix']) ? $conf['suffix'] : null;
        $prefix = isset($conf['prefix']) ? $conf['prefix'] : null;
        $countOption = isset($conf['count_option']) ? $conf['count_option'] : null;
        $items = self::getRecordData($recordSetSlug, $fields, [], $filter, 10000);
        if ($items && isset($items['Items'])) {
            $count = 0;
            if ($countOption == 'count') {
                $count = count($items['Items']);
                return array(
                    'count' => $prefix .$count. $suffix,
                    'backgroundcolor' => $backgroundcolor
                );
            } else {
                foreach ($items as $item) {
                    foreach ($item as $child) {
                        if (isset($child['ItemData'])) {
                            foreach ($child['ItemData'] as $id) {
                                $val = explode(',', $id['Value']);
                                $count += (float) preg_replace('/[^0-9]/', '', $val[0]);
                            }
                        }
                    }
                }
                return array(
                    'count' => $prefix . number_format($count, 2, ',', '.') . $suffix,
                    'backgroundcolor' => $backgroundcolor
                );
            }
        }
    }

    /**
     * This function returns labels and data for the linear chart 
     * @param DataObject $widget
     * @return array
     */
    public static function linearChartData($widget) {
        $conf      = self::getWidgetConfig($widget->Configurations(), 'widget_linearChart');
        if(!$conf) return;
        $dataset = isset($conf['dataset']) ? $conf['dataset'] : null;
        $interval = isset($conf['interval']) ? $conf['interval'] : null;
        return array(
            'labels' => $interval && isset($interval['interval']) ? self::getLabelsbyInterval($interval['interval'], $interval['intervalType']): self::getLabelsFromDataset($dataset),
            'datasets' => self::getChartDatasets($dataset, $widget->Type, $interval)
        );

        /*  return array(
                array(
                    'label' => 'First Dataset',
                    'data' => array(65, 59, 80, 81, 56, 55, 40),
                    'fill' => true,
                    'backgroundColor' => 'rgb(255, 205, 86)',
                    'borderColor' => 'rgb(255, 205, 86)'
                ), array(
                    'label' => 'Second Dataset',
                    'data' => array(28, 48, 40, 19, 86, 27, 90),
                    'fill' => true,
                    'backgroundColor' => 'rgb(75, 192, 192)',
                    'borderColor' => 'rgb(75, 192, 192)'
                ), array(
                    'label' => 'third Dataset',
                    'data' => array(22, 52, 44, 54, 92, 41, 101),
                    'fill' => false,
                    'backgroundColor' => 'rgb(102, 192, 192)',
                    'borderColor' => 'rgb(102, 192, 192)'
                )
              
            );
          */
    }

    /**
     * This function returns labels and data for the bar chart 
     * @param DataObject $widget
     * @return array
     */    
    public static function barChartData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_barChart');
        if(!$conf) return;
        $dataset = isset($conf['dataset']) ? $conf['dataset'] : null;
        $interval = isset($conf['interval']) ? $conf['interval'] : null;
        return array(
            'labels' => $interval && isset($interval['interval']) ? self::getLabelsbyInterval($interval['interval'], $interval['intervalType']): self::getLabelsFromDataset($dataset),
            'datasets' => self::getChartDatasets($dataset, $widget->Type, $interval)
        );

       /* return array(
            'labels' => array('January', 'February', 'March', 'April', 'May', 'June', 'July'),
            'datasets' => array(
                array(
                    'label' => 'First Dataset',
                    'data' => array(65, 59, 80, 81, 56, 55, 40),
                    'backgroundColor' => 'rgb(255, 205, 86)',
                    'borderColor' => 'rgb(255, 205, 86)'
                ), array(
                    'label' => 'Second Dataset',
                    'data' => array(28, 48, 40, 19, 86, 27, 90),
                    'backgroundColor' => 'rgb(75, 192, 192)',
                    'borderColor' => 'rgb(75, 192, 192)'
                )
            )
        );
        * 
        */
    }

    /**
     * This function returns labels and data for the pie chart 
     * @param DataObject $widget
     * @return array
     */
    public static function pieChartData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_pieChart');
        if(!$conf) return;
        $dataset = isset($conf['dataset']) ? $conf['dataset'] : null;
        $interval = isset($conf['interval']) ? $conf['interval'] : null;
        return array(
            'labels' => $interval && isset($interval['interval']) ? self::getLabelsbyInterval($interval['interval'], $interval['intervalType']): self::getLabelsFromDataset($dataset),
            'datasets' => self::getChartDatasets($dataset, $widget->Type, $interval)
        );
    }

    /**
     * This function returns labels and data for the polar area chart 
     * @param DataObject $widget
     * @return array
     */
    public static function polarAreaChartData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_polarAreaChart');
        if(!$conf) return;
        $dataset = isset($conf['dataset']) ? $conf['dataset'] : null;
        $interval = isset($conf['interval']) ? $conf['interval'] : null;
        return array(
            'labels' => $interval && isset($interval['interval']) ? self::getLabelsbyInterval($interval['interval'], $interval['intervalType']): self::getLabelsFromDataset($dataset),
            'datasets' => self::getChartDatasets($dataset, $widget->Type, $interval)
        );
    }

    /**
     * This function returns labels and data for the doughnut chart 
     * @param DataObject $widget
     * @return array
     */
    public static function doughnutChartData($widget) {
        $conf = self::getWidgetConfig($widget->Configurations(), 'widget_doughnutChart');
        if(!$conf) return;
        
        $dataset = isset($conf['dataset']) ? $conf['dataset'] : null;
        $interval = isset($conf['interval']) ? $conf['interval'] : null;
        return array(
            'labels' => $interval && isset($interval['interval']) ? self::getLabelsbyInterval($interval['interval'], $interval['intervalType']): self::getLabelsFromDataset($dataset),
            'datasets' => self::getChartDatasets($dataset, $widget->Type, $interval)
        );
    }

    /**
     * Planned for the release EveryDataStore v1.5
     * This function returns weather data for the location specified in 
     * 'widget_weather_location' configuration 
     * @param DataObject $widget
     * @return array
     */
    public static function weatherData($widget) {
        /*$config = self::getWidgetConfig($widget->Configurations(), 'widget_weather_location');
        if ($config) {
          
            $httpRequestFactory = new RequestFactory();
            $httpClient = GuzzleAdapter::createWithConfig([]);
            $owm = new OpenWeatherMap('{}', $httpClient, $httpRequestFactory);
            //$lang = 'de';
            //$units = 'metric';
            $weather = $owm->getWeather($config[0]['location'], $config[0]['unit'], self::getMemberLanguageCode());


            $ret = array(
                'location' => $config[0]['location'],
                'temperature' => round($weather->temperature->getValue()),
                'temperatureUnit' => $weather->temperature->getUnit(),
                'now' => $weather->temperature->now,
                'min' => $weather->temperature->min,
                'max' => $weather->temperature->max,
                'lastUpdate' => self::getNiceDateTimeFormat($weather->lastUpdate->format('r')),
                'pressure' => $weather->pressure,
                'humidity' => $weather->humidity,
                'sunrise' => date_format(date_create($weather->sun->rise->format('r')), 'H:i'),
                'sunset' => date_format(date_create($weather->sun->set->format('r')), 'H:i'),
                'icon' => 'http://openweathermap.org/img/wn/' . $weather->weather->icon . '.png'
            );
            return $ret;
        }
         * 
         */
    }

    /**
     * 
     * @param DataObject $widget
     * @return array
     */
    public static function clockData($widget) {
        return array($widget->ID);
    }

    /** 
     * CalendarRecordSlug
     * @param DataObject $widget
     * @return array
     */
    public static function calendarData($widget) {
        $calendarRecordSlug = self::getEveryConfig('CalendarRecordSlug');
        $calendarEventTitleSlug = self::getEveryConfig('CalendarEventTitleSlug');
        $calendarEventStartDateSlug = self::getEveryConfig('CalendarEventStartDateSlug');
        $calendarEventEndDateSlug = self::getEveryConfig('CalendarEventEndDateSlug');
        $calendarEventBackgroundSlug = self::getEveryConfig('CalendarEventBackgroundSlug');
        $recordSet = $calendarRecordSlug ? RecordSet::get()->filter(['Slug' => $calendarRecordSlug, 'Active' => true, 'DataStoreID' => self::getCurrentDataStoreID()])->first() : null;
        $ret = [];

        if ($recordSet) {
            if ($recordSet->Items()->Count() > 0) {
                $items = $recordSet->Items()->filter(['Version:GreaterThan' => 0])->Sort("Created DESC")->limit(10000);
                $i = 1;
                foreach ($items as $item) {
                    if ($item->ItemData()->Count() > 0) {
                        $ret[] = array(
                            'id' => $i,
                            'title' => self::getItemDataValueByFormField($item->ItemData(), $calendarEventTitleSlug),
                            'start' => self::getItemDataValueByFormField($item->ItemData(), $calendarEventStartDateSlug),
                            'end' => self::getItemDataValueByFormField($item->ItemData(), $calendarEventEndDateSlug),
                            'color' => self::getItemDataValueByFormField($item->ItemData(), $calendarEventBackgroundSlug),
                            'recordItemSlug' => $item->Slug,
                        );
                        $i++;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * This function returns data regarding Member's last login attempt
     * @param DataObject $widget
     * @return array
     */
    public static function lastLoginData($slug) {
        $lastLogin = self::getMemberLastLogin();
        if ($lastLogin) {
            return array(
                'Status' => $lastLogin->Status,
                'IP' => $lastLogin->IP,
                'Date' => $lastLogin->Created,
            );
        }
    }

    /**
     * This function returns data for the Record with provided $slug
     * @param string $slug
     * @param array $fields
     * @param array $values
     * @param array $filter
     * @param integer $limit
     * @return array
     */
    public static function getRecordData($slug, $fields = [], $filter1 = [], $filter2 = [], $limit = 10000, $sortcolumn = "Created", $sortdir = "ASC" ) {
       $recordSet = Versioned::get_by_stage('EveryDataStore\Model\RecordSet\RecordSet', Versioned::LIVE)->filter(['Slug' => $slug, 'Active' => true, 'DataStoreID' => self::getCurrentDataStoreID()])->first();
        if ($recordSet) {
            $labels = $recordSet->RecordResultlistLabels();
            $items = $recordSet->getNiceItems();
            if($filter1){
              $items =  $items->filter($filter1);
            }
            if($filter2){
              $items = $items->filter($filter2);
            }
            $items = $items->Sort($sortcolumn, $sortdir)->limit($limit);
            
            $niceItems = [];
            $fieldsFilter = [
                    'FormField.Slug' => $fields,
                    'Value:not' => [null, '']
                ];

            foreach ($items as $item) {
                $itemData = $fields ? $item->ItemData()->filter($fieldsFilter)->Sort("ID DESC") : $item->ItemData()->Sort("ID DESC");
                $niceItemData = [];
                foreach ($itemData as $id) {
                    $value = $id->Value();
                        $niceItemData[] = array(
                            "Value" => $value ? $value : null,
                            "Type" => $id->FormField()->getType(),
                            "Created" => $id->Created,
                            "FormFieldSlug" => $id->FormField()->Slug
                        );
                }
                if ($niceItemData && $filter && isset($filter['Value']) && $fields) {
                    foreach ($niceItemData as $k => $v) {
                        if ($v['FormFieldSlug'] == $fields[0]) {
                            if (!in_array($v["Value"], $filter['Value'])) {
                                unset($niceItemData[$k]);
                                if(count($fields) > 0) unset($niceItemData[$k+1]);
                            }
                        }
                    }
                }

                if (!empty($niceItemData)) {
                    $niceItems[] = array(
                        'Slug' => $item->Slug,
                        'Created' => $item->Created,
                        'LastEdited' => $item->LastEdited,
                        'ItemData' => $niceItemData
                    );
                }
            }

            return array(
                'Labels' => $labels,
                'Items' => $niceItems,
                'Name' => $recordSet->Title,
                'Slug' => $recordSet->Slug,
            );
        }
    }
    
    /**
     * This function gets widget data from widget.json
     * @param string $slug
     * @return array
     */
    public static function getWidgetData($slug, $type = null) {
        if($type == 'lastLogin'){
            return self::lastLoginData($slug);
        }
        $widgetsFile = ASSETS_PATH . '/.protected/'.str_replace(' ', '-',self::getCurrentDataStore()->Folder()->Filename).'widgets.json';
        if ($widgetsFile) {
            if (file_exists($widgetsFile)) {
                $content = json_decode(file_get_contents($widgetsFile), true);
                if (isset($content[$slug])) {
                    return $content[$slug];
                }
            }
        }
    }

    /**
     * This function returns configurations with the title starting with $configName
     * @param array $widgetConfigs
     * @param string $configName
     * @return array
     */
    private static function getWidgetConfig($widgetConfigs, $configName) {
        if (!empty($widgetConfigs)) {
            /*if($widgetConfigs->first()->Value && $widgetConfigs->first()->Value){
                return self::isJson($widgetConfigs->first()->Value) ? json_decode($widgetConfigs->first()->Value, true) : null;
            }*/
            
            $config = $widgetConfigs->filter(['Title:StartsWith' => $configName, 'DataStoreID' => self::getCurrentDataStoreID()])->first();

            if ($config) {
                return self::isJson($config->Value) ? json_decode($config->Value, true) : $config->Value;
            }
        }
        return null;
    }

    /**
     * This function returns value for the field with $formFieldSlug
     * @param DataObject $itemData
     * @param string $formFieldSlug
     * @return DataObject
     */
    private static function getItemDataValueByFormField($itemData, $formFieldSlug) {
        $item = $itemData ? $itemData->filter(['FormField.Slug' => $formFieldSlug])->first() : null;
        return $item ? $item->Value() : null;
    }

    /**
     * This function filters labels from configuration
     * @param array $config
     * @return array
     */
    private static function getChartConfigProperty($config, $property) {
        $ret = [];
        foreach ($config as $c) {
            $ret[] = $c[$property];
        }
        return $ret;
    }

    /**
     * This function returns for Record data either a number of record items or 
     * a sum of total values in record items
     * @param array $config
     * @param array $args
     * @return array
     */
    private static function getChartData($config, $args = []) {
        $ret = [];
        $i=0;
        foreach ($config as $c) {
            if ($c['dataType'] == 'RecordSet') {
                $ret[] = self::getChartRecordData($c, $args, $i);
            } else {
                $ret[] = self::getChartModelData($c, $args, $i);
            }
            $i++;
        }
        //self::pr($ret);
        return $ret;
    }
    
    /**
     * This function returns chart data with appropriate filtering option
     * @param array $config
     * @param array $args
     * @return array
     */
    private static function getChartComplextData($conf, $args) {
        $ret = [];
        
        for($i = 0; $i <= $args['interval']; $i++) {
            $itemsfilter = [];
             if ($args['intervalType'] == 'month') {
                $itemsfilter = ['Created:GreaterThanOrEqual' => date('Y-m-01', strtotime('-'.$i.' month')), 'Created:LessThanOrEqual' => date('Y-m-t', strtotime(date('Y-m', strtotime('-'.$i.' month'))))];
            } elseif ($args['intervalType'] == 'year') {
                $itemsfilter = ['Created:GreaterThanOrEqual' => date('Y-01-01', strtotime('-'.$i.' year')), date('Y-12-31', strtotime('-'.$i.' year'))];
            } else {
                $itemsfilter = ['Created:GreaterThanOrEqual' => date('Y-m-d 00:00:01', strtotime('-'.$i.' day')), 'Created:LessThanOrEqual' => date('Y-m-d 23:59:59', strtotime('-'.$i.' day'))];
            }
           
            if ($conf['dataType'] == 'RecordSet') {
                $ret[] = self::getChartRecordData($conf,$args, $i, $itemsfilter);
            } else {
                $ret[] = self::getChartModelData($conf, $itemsfilter);
            }
        }
        
        
        return $ret;
    }

    /**
     * This function returns either a number of record items or a sum of total values in record items
     * @param array $conf
     * @param array $args
     * @param integer $index
     * @return float
     */
    private static function getChartRecordData($conf, $args = [], $index = 0, $itemsFilter = []) {
        $slug = isset($conf['slug']) ? $conf['slug']: null;
        $fields = isset($conf['fields']) ? $conf['fields']: null;
        $filter = isset($conf['filter']) && !empty($conf['filter'])? ($conf['filter']): self::getFilterFromInterval($args)[$index];
        $sortcolumn =  isset($conf['sortcolumn']) ? $conf['sortcolumn']: 'Created';
        $sortdir =   isset($conf['sortdir']) ? $conf['sortdir']: 'DESC';
        $limit = $conf && isset($conf['limit'])  ? $conf['limit']: 10000;
        $countOption = $conf && isset($conf['countOption'])  ? $conf['countOption']: 'count';
        $sum = 0;

        $recordSetData = self::getRecordData($slug, $fields, $itemsFilter, $filter, $limit, $sortcolumn, $sortdir);
        if ($recordSetData && isset($recordSetData['Items'])) {
            if ($countOption == 'count') {
                return count($recordSetData['Items']);
            } else {
                $sum = 0;
                foreach ($recordSetData['Items'] as $r) {
                    foreach ($r['ItemData'] as $d) {
                        $sum += $d['Value'];
                    }
                }
                return $sum;
            }
        }
    }
    /**
     * Coming soon
     * @param int $config
     * @param int $filter
     */
    private static function getChartModelData($config, $filter = []) {}

    /**
     * This function retrieves chart data and sets char appearance according to $config
     * @param array $config
     * @param string $type
     * @param array $args
     * @return array
     */
    private static function getChartDatasets($config, $type, $args = null) {
        if ($type == 'doughnutChart' || $type =='pieChart' || $type == 'polarAreaChart') {
            return array(
                array(
                    'data' =>  self::getChartData($config, $args),
                    'backgroundColor' => self::getChartConfigProperty($config, 'backgroundColor'),
                    'hoverBackgroundColor' => self::getChartConfigProperty($config, 'hoverBackgroundColor'),
                )
            );
        }else if($type == 'linearChart' || $type == 'barChart'){
           
           $dataset = [];
           foreach ($config as $c){
               $dataset[] = 
                  array(
                    'label' => $c['label'],
                    'data' => self::getChartComplextData($c, $args),
                    'fill' => isset($c['fill']) && $c['fill'] == 'true' ? true : false,
                    'backgroundColor' => isset($c['backgroundColor']) ? $c['backgroundColor'] : 'rgb(0, 0, 0)',
                    'borderColor' => isset($c['borderColor']) ? $c['borderColor'] : 'rgb(255,255,255)',
                    
               );
           }        
          return $dataset;
        }
    }
    
    /**
     * This function returns a list of labels corresponding to the given $type and $interval 
     * @param integer $interval
     * @param string $type
     * @param integer $numberMonth
     * @return array
     */
    private static function getLabelsbyInterval($interval, $type, $numberMonth = false) {
        $labels = [];
  
        setlocale(LC_TIME, self::getMember()->Locale);
        if ($type == 'year') {
            for($y=0; $y<=$interval; $y++ ){
                $labels[] = date ('y', strtotime ( '-'.$y.' month' , strtotime ( date('Y-m-d') )));
            }
        } else if ($type == 'month' && $interval <= 12) {
            for($m=0; $m<=$interval; $m++ ){
                $labels[] = date ('m', strtotime ( '-'.$m.' month' , strtotime ( date('Y-m-d') ))).'/'.date ('Y', strtotime ( '-'.$m.' month' , strtotime ( date('Y-m-d') )));
            }
        } else {
            for($d=0; $d<=$interval; $d++ ){
                $labels[] = date ('d', strtotime ( '-'.$d.' day' , strtotime ( date('Y-m-d') ))).'/'.date ('m/Y', strtotime ( '-'.$d.' days' , strtotime ( date('Y-m-d') )));;
            }
        }
        
        #return array_reverse($labels);
        return $labels;
    }
    
    /**
     * This function returns a list of labels from config
     * @param array $config
     * @return array
     */
    private static function getLabelsFromDataset($config) {
        $labels = [];
  
        foreach($config as $c){
            $labels[] = isset($c['label']) ? $c['label'] : '';
        }
        
        return $labels;
    }
    
    /**
     * This function returns nice filter for DataObject filtering
     * @param array $args
     * @return array
     */
    private static function getFilterFromInterval($args) {
        $filter = [];
        for ($i = 0; $i <= $args['interval']; $i++) {
            if ($args['intervalType'] == 'month') {
                $filter[] = ['Created:GreaterThanOrEqual' => date('Y-m-01', strtotime('-'.$i.' month')), 'Created:LessThanOrEqual' => date('Y-m-t', strtotime(date('Y-m', strtotime('-'.$i.' month'))))];
            } elseif ($args['intervalType'] == 'year') {
                $filter[] = ['Created:GreaterThanOrEqual' => date('Y-01-01', strtotime('-'.$i.' year')), date('Y-12-31', strtotime('-'.$i.' year'))];
            } else {
                $filter[] = ['Created:GreaterThanOrEqual' => date('Y-m-d 00:00:01', strtotime('-'.$i.' day')), 'Created:LessThanOrEqual' => date('Y-m-d 23:59:59', strtotime('-'.$i.' day'))];
            }
        }
        return $filter;
    }
}
