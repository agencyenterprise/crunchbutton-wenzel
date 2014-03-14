<?php

class Crunchbutton_Group extends Cana_Table {

	const DRIVER_GROUPS_PREFIX = 'drivers-';

	public function driverGroupOfCommunity( $community ){
		return Crunchbutton_Group::normalizeDriverGroup( str_replace( ' ' , '-', Crunchbutton_Group::DRIVER_GROUPS_PREFIX . strtolower( str_replace( "'", '', str_replace( '"', '', str_replace( ".", '', $community ) ) ) ) ), 0, 20);
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('group')
			->idVar('id_group')
			->load($id);
	}

	public function getRestaurantCommunityName( $community ){
		$communities = Restaurant::getCommunities();
		foreach( $communities as $_community ){
			if( Crunchbutton_Group::driverGroupOfCommunity( $_community ) == $community ){
				return $_community;
			}
		}	
	}

	public function normalizeDriverGroup( $community ){
		return substr( $community, 0, 20 );
	}

	public function getDeliveryGroupByCommunity( $community ){
		
		if( !$community ){
			die( 'Error:getDeliveryGroupByCommunity' );
		}

		$community = Crunchbutton_Group::normalizeDriverGroup( $community );

		$group = Crunchbutton_Group::byName( $community );
		if( $group->id_group ){
			return $group;
		}

		// Get the community name
		$description = Crunchbutton_Group::getRestaurantCommunityName( $community );

		$description .= ' drivers group';
		$group = new Crunchbutton_Group();
		$group->name = $community;
		$group->description = $description;
		$group->save();
		return $group;
	}

	public function createDriverGroup( $name, $description ){
		$description = $description . ' drivers group';
		$group = new Crunchbutton_Group();
		$group->name = $name;
		$group->description = $description;
		$group->save();
		return $group;
	}

	public function permissions(){
		if( !$this->_permissions ){
			$this->_permissions = c::db()->get( "SELECT * FROM admin_permission WHERE id_group = {$this->id_group}" );	
		}
		return $this->_permissions;
	}

	public function hasPermission( $permission, $useRegex = false ){
		$permissions = $this->permissions();
		foreach( $permissions as $_permission ){
			if( $_permission->permission == $permission && $_permission->allow == 1 ){
				return true;
			}
			if( $useRegex ){
				if( preg_match( $permission, $_permission->permission )  && $_permission->allow == 1 ){
					return true;
				}
			}
		}
		return false;
	}


	public static function find($search = []) {

		$query = 'SELECT `group`.* FROM `group` WHERE id_group IS NOT NULL ';
		
		if ( $search[ 'name' ] ) {
			$query .= " AND name LIKE '%{$search[ 'name' ]}%' ";
		}

		$query .= " ORDER BY name DESC";

		$groups = self::q($query);
		return $groups;
	}

	public function users(){
		if( $this->id_group ){
			return Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" );	
		} 
		return false;
	}

	public function removeUsers(){
		c::db()->query( "DELETE FROM admin_group WHERE id_group = {$this->id_group}" );
	}

	public function removePermissions(){
		c::db()->query( "DELETE FROM admin_permission WHERE id_group = {$this->id_group}" );
	}

	public function byName( $name ){
		return Crunchbutton_Group::q( "SELECT * FROM `group` WHERE name='{$name}'" );
	}

	public function addPermissions( $permissions ){
		if( $permissions && is_array( $permissions ) ){
			foreach( $permissions as $key => $val ){
				if( !$this->hasPermission( $key ) ){
					$_permission = new Crunchbutton_Admin_Permission();
					$_permission->id_group = $this->id_group;
					$_permission->permission = trim( $key );
					$_permission->allow = 1;
					$_permission->save();
					// reset the permissions
					$this->_permissions = false;
					$dependencies = $_permission->getDependency( $key );
					if( $dependencies ){
						foreach( $dependencies as $dependency ){
							$this->addPermissions( array( $dependency => 1 ) );
						}
					}
				}
			}
		}
	}

	public function hasUser( $id_admin ){
		$admin_group = Crunchbutton_Admin_Group::q( "SELECT * FROM admin_group ag WHERE ag.id_group = {$this->id_group} AND ag.id_admin = {$id_admin} LIMIT 1" );
		if( $admin_group->id_admin_group ){
			return true;
		}
		return false;
	}

	public function usersTotal(){
		if( $this->id_group ){
			return Crunchbutton_Admin_Group::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$this->id_group}" )->count();	
		} 
		return 0;
	}

}