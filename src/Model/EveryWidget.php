<?php

namespace EveryWidget\Model;

use EveryWidget\Helper\EveryWidgetHelper;
use EveryDataStore\Model\DataStore;
use EveryDataStore\Model\EveryConfiguration;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Core\ClassInfo;

class EveryWidget extends DataObject implements PermissionProvider 
{
    private static $table_name = 'EveryWidget';
    private static $singular_name = 'EveryWidget';
    private static $plural_name = 'EveryWidgets';
    private static $db = [
        'Slug' => 'Varchar(110)',
        'Active' => 'Boolean',
        'Title' => 'Varchar(110)',
        'Description' => 'Varchar(110)',
        'Type' => 'Varchar(40)',
        'Sort' => 'Int(11)'
    ];
    
    private static $default_sort = "Sort";
    private static $has_one = [
        'DataStore' => DataStore::class
    ];
    
    private static $many_many = [
        'Configurations' => EveryConfiguration::class,
        'Groups' => Group::class,
    ];
    
    private static $has_many = [];
    private static $summary_fields = [
        'Active',
        'Type',
        'Title',
        'Description'
    ];
    
    private static $searchable_fields = [
        'Title' => [
            'field' => TextField::class,
            'filter' => 'PartialMatchFilter',
        ],
        'Description' => [
            'field' => TextField::class,
            'filter' => 'PartialMatchFilter',
        ],
        'Type' => [
            'field' => TextField::class,
            'filter' => 'PartialMatchFilter',
        ]
    ];
    
    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels(true);
        if(!empty(self::$summary_fields)){
           $labels = EveryWidgetHelper::getNiceFieldLabels($labels, __CLASS__, self::$summary_fields);
        }
        return $labels;
    }
    
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab('Root.Main', ['DataStoreID', 'Configurations', 'Groups']);
        $fields->addFieldToTab('Root.Main', HiddenField::create('Slug', 'Slug', $this->Slug));
        $fields->addFieldToTab('Root.Main', CheckboxField::create('Active', _t(__Class__ .'.ACTIVE', 'Active')));
        $fields->addFieldToTab('Root.Main', DropdownField::create('Type', _t(__Class__ .'.TYPE', 'Type'), Config::inst()->get(__CLASS__, 'Types')));
        $fields->addFieldToTab('Root.Main', TextField::create('Title', _t(__Class__ .'.TITLE', 'Title')));
        $fields->addFieldToTab('Root.Main', TextField::create('Description', _t(__Class__ .'.DESCRIPTION', 'Description')));
        $fields->addFieldToTab('Root.Main', TextField::create('Sort', _t(__Class__ .'.SORT', 'Sort')));

        //$fields->addFieldToTab('Root.Main', ListboxField::create('Groups', _t(__Class__ .'.GROUPS', 'Groups'), EveryWidgetHelper::getCurrentDataStore()->Groups()->Map(EveryWidgetHelper::getMapField(), 'Title')->toArray()));
        //$fields->addFieldToTab('Root.Main', ListboxField::create('Configurations', _t(__Class__ .'.CONFIGURATIONS', 'Configurations'), EveryWidgetHelper::getCurrentDataStore()->Configurations()->Map(EveryWidgetHelper::getMapField(), 'Title')->toArray()));
        //$fields->addFieldToTab('Root.Main', ReadonlyField::create('Counter', 'Counter', $this->Slug));
        return $fields;
    }
     
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if (!$this->Slug) {
            $this->Slug = EveryWidgetHelper::getAvailableSlug(__CLASS__);
        }
        
        if (!$this->DataStoreID) {
            $this->DataStoreID =  EveryWidgetHelper::getCurrentDataStoreID();
        }
        if(!$this->Sort){
           $this->Sort = $this->ID; 
        }

    }

    public function onAfterWrite() {
        parent::onAfterWrite();
    }

    public function onBeforeDelete() {
        parent::onBeforeDelete();
    }

    public function onAfterDelete() {
        parent::onAfterDelete();
    }
    
    public function Title(){
        return EveryWidgetHelper::_t($this->Title);
    }
    
    public function WidgetData() {
       if($this->Type && $this->ID > 0){
            return EveryWidgetHelper::getWidgetData($this->Slug);
        }
        
    }

    /**
     * This function should return true if the current user can view an object
     * @see Permission code VIEW_CLASSSHORTNAME e.g. VIEW_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @return bool True if the the member is allowed to do the given action
     */
    public function canView($member = null) {
        return EveryWidgetHelper::checkPermission(EveryWidgetHelper::getNicePermissionCode("VIEW", $this));
    }

    /**
     * This function should return true if the current user can edit an object
     * @see Permission code VIEW_CLASSSHORTNAME e.g. EDIT_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @return bool True if the the member is allowed to do the given action
     */
    public function canEdit($member = null) {
        return EveryWidgetHelper::checkPermission(EveryWidgetHelper::getNicePermissionCode("EDIT", $this));
    }

    /**
     * This function should return true if the current user can delete an object
     * @see Permission code VIEW_CLASSSHORTNAME e.g. DELTETE_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @return bool True if the the member is allowed to do the given action
     */
    public function canDelete($member = null) {
        return EveryWidgetHelper::checkPermission(EveryWidgetHelper::getNicePermissionCode("DELETE", $this));
    }

    /**
     * This function should return true if the current user can create new object of this class.
     * @see Permission code VIEW_CLASSSHORTNAME e.g. CREATE_MEMBER
     * @param Member $member The member whose permissions need checking. Defaults to the currently logged in user.
     * @param array $context Context argument for canCreate()
     * @return bool True if the the member is allowed to do this action
     */
    public function canCreate($member = null, $context = []) {
        return EveryWidgetHelper::checkPermission(EveryWidgetHelper::getNicePermissionCode("CREATE", $this));
    }

    /**
     * Return a map of permission codes for the Dataobject and they can be mapped with Members, Groups or Roles
     * @return array 
     */
    public function providePermissions() {
        return array(
            EveryWidgetHelper::getNicePermissionCode("CREATE", $this) => [
                'name' => _t('SilverStripe\Security\Permission.CREATE', "CREATE"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
            ],
            EveryWidgetHelper::getNicePermissionCode("EDIT", $this) => [
                'name' => _t('SilverStripe\Security\Permission.EDIT', "EDIT"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
            ],
            EveryWidgetHelper::getNicePermissionCode("VIEW", $this) => [
                'name' => _t('SilverStripe\Security\Permission.VIEW', "VIEW"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
            ],
            EveryWidgetHelper::getNicePermissionCode("DELETE", $this) => [
                'name' => _t('SilverStripe\Security\Permission.DELETE', "DELETE"),
                'category' => ClassInfo::shortname($this),
                'sort' => 1
        ]);
    }
}
