<?php namespace TriTan;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

use TriTan\Config;

/**
 * Access Control Level Class
 *
 * @license GPLv3
 *         
 * @since 1.0.0
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class ACL
{

    /**
     * Stores the permissions for the user
     *
     * @access public
     * @var array
     */
    protected $_perms = [];

    /**
     * Stores the ID of the current user
     *
     * @access public
     * @var integer
     */
    protected $_userID = 0;

    /**
     * Stores the roles of the current user
     *
     * @access public
     * @var array
     */
    protected $_userRoles = [];
    public $app;

    public function __construct($userID = '')
    {
        $this->app = \Liten\Liten::getInstance();

        if ($userID != '') {
            $this->_userID = floatval($userID);
        } else {
            $this->_userID = floatval(get_current_user_id());
        }
        $this->_userRoles = $this->getUserRoles('ids');
        $this->buildACL();
    }

    public function ACL($userID = '')
    {
        $this->__construct($userID);
    }

    public function buildACL()
    {
        //first, get the rules for the user's role
        if (count($this->_userRoles) > 0) {
            $this->_perms = array_merge($this->_perms, $this->getRolePerms($this->_userRoles));
        }
        //then, get the individual user permissions
        $this->_perms = array_merge($this->_perms, $this->getUserPerms($this->_userID));
    }

    public function getPermKeyFromID($permID)
    {
        $permission = $this->app->db->table('permission')
            ->where('permission_id', floatval($permID))
            ->first();

        return _escape($permission['permission_key']);
    }

    public function getPermNameFromID($permID)
    {
        $permission = $this->app->db->table('permission')
            ->where('permission_id', floatval($permID))
            ->first();

        return _escape($permission['permission_name']);
    }

    public function getRoleNameFromID($roleID)
    {
        $role = $this->app->db->table('role')
            ->where('role_id', floatval($roleID))
            ->first();

        return _escape($role['role_name']);
    }

    public function getUserRoles()
    {
        $strSQL = $this->app->db->table('user_roles')
            ->where('user_id', floatval($this->_userID))
            ->sortBy('add_date', 'ASC')
            ->get();

        $resp = [];
        foreach ($strSQL as $r) {
            $resp[] = _escape($r['role_id']);
        }

        return $resp;
    }

    public function getAllRoles($format = 'ids')
    {
        $_format = strtolower($format);

        $strSQL = $this->app->db->table('role')
            ->sortBy('role_name', 'ASC')
            ->get();

        $resp = [];
        foreach ($strSQL as $r) {
            if ($_format == 'full') {
                $resp[] = [ "ID" => _escape($r['role_id']), "Name" => _escape($r['role_name'])];
            } else {
                $resp[] = $r->id;
            }
        }
        return $resp;
    }

    public function getAllPerms($format = 'ids')
    {
        $_format = strtolower($format);

        $strSQL = $this->app->db->table('permission')
            ->sortBy('permission_name', 'ASC')
            ->get();

        $resp = [];
        foreach ($strSQL as $r) {
            if ($_format == 'full') {
                $resp[$r['permission_key']] = [ 'ID' => _escape($r['permission_id']), 'Name' => _escape($r['permission_name']), 'Key' => _escape($r['permission_key'])];
            } else {
                $resp[] = _escape($r->permission_id);
            }
        }
        return $resp;
    }

    public function getRolePerms($role)
    {
        if (is_array($role)) {
            $roleSQL = $this->app->db->table('role_perms')
                ->where('role_id', 'IN', implode(",", $role))
                ->sortBy('role_perms_id', 'ASC')
                ->get();
        } else {
            $roleSQL = $this->app->db->table('role_perms')
                ->where('role_id', '=', floatval($role))
                ->sortBy('role_perms_id', 'ASC')
                ->get();
        }

        $perms = [];
        foreach ($roleSQL as $r) {
            $pK = strtolower($this->getPermKeyFromID($r['permission_id']));
            if ($pK == '') {
                continue;
            }
            if ($r->value === '1') {
                $hP = true;
            } else {
                $hP = false;
            }
            $perms[$pK] = [ 'perm' => $pK, 'inheritted' => true, 'value' => $hP, 'Name' => $this->getPermNameFromID(_escape($r['permission_id'])), 'ID' => _escape($r['permission_id'])];
        }
        return $perms;
    }

    public function getUserPerms($userID)
    {
        $strSQL = $this->app->db->table('user_perms')
            ->where('user_id', floatval($userID))
            ->sortBy('modified', 'ASC')
            ->get();

        $perms = [];
        foreach ($strSQL as $r) {
            $pK = strtolower($this->getPermKeyFromID(_escape($r['permission_id'])));
            if ($pK == '') {
                continue;
            }
            if ($r->value === '1') {
                $hP = true;
            } else {
                $hP = false;
            }
            $perms[$pK] = [ 'perm' => $pK, 'inheritted' => false, 'value' => $hP, 'Name' => $this->getPermNameFromID(_escape($r['permission_id'])), 'ID' => _escape($r['permission_id'])];
        }
        return $perms;
    }

    public function userHasRole($roleID)
    {
        foreach ($this->_userRoles as $v) {
            if (floatval($v) === floatval($roleID)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission($permKey)
    {
        $user_role = $this->app->db->table('usermeta')
            ->where('meta_key', Config::get('tbl_prefix') . 'user_role')
            ->where('user_id', get_current_user_id())
            ->first();

        $perms = $this->app->db->table('role')
            ->where('role_id', (int) _escape($user_role['meta_value']))
            ->first();

        $perm = app()->hook->{'maybe_unserialize'}(_escape($perms['role_permission']));

        if (in_array($permKey, $perm)) {
            return true;
        }
        return false;
    }

    public function getUsername($id)
    {
        $strSQL = $this->app->db->table('user')
            ->where('user_id', floatval($id))
            ->first();
        return _escape($strSQL['user_login']);
    }
}