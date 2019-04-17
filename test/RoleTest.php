<?php

require_once('lib/bootstrap.php');

class RoleTest extends KirbyTestCase {

  protected $role;

  public function setUp(): void {
    $this->role = new Role(array(
      'id'          => 'testrole',
      'name'        => 'Test role',
      'permissions' => array(
        '*'                 => true,
        'panel.site.update' => false,
        'panel.user.*'      => false,
        'panel.user.error'  => 'this is invalid',
        'panel.user.update' => function($user) {
          switch($user) {
            case 'testuser':
              return true;
            case 'failuser1':
              return false;
            case 'failuser2':
              return 'This user should fail.';
            case 'returnerror':
              return 123;
            default:
              throw new Error('Invalid user.');
          }
        }
      )
    ));
  }

  public function testMeta() {
    $this->assertEquals('testrole', $this->role->id());
    $this->assertEquals('Test role', $this->role->name());
    $this->assertFalse($this->role->isDefault());
    $this->role->default = true;
    $this->assertTrue($this->role->isDefault());
  }

  public function testPermission() {
    $result = $this->role->permission('testpermission');
    $this->assertTrue($result->status());
    $this->assertNull($result->message());

    $result = $this->role->permission('panel.site.update');
    $this->assertFalse($result->status());
    $this->assertNull($result->message());

    $result = $this->role->permission('panel.user.test');
    $this->assertFalse($result->status());
    $this->assertNull($result->message());

    $result = $this->role->permission('panel.user.update', 'testuser');
    $this->assertTrue($result->status());
    $this->assertNull($result->message());

    $result = $this->role->permission('panel.user.update', 'failuser1');
    $this->assertFalse($result->status());
    $this->assertNull($result->message());

    $result = $this->role->permission('panel.user.update', 'failuser2');
    $this->assertFalse($result->status());
    $this->assertEquals('This user should fail.', $result->message());

    $event  = new Kirby\Event('panel.user.update');
    $result = $this->role->permission($event, 'failuser2');
    $this->assertFalse($result->status());
    $this->assertEquals('This user should fail.', $result->message());
  }

  public function testPermissionInvalidEvent() {
    $this->expectException('Error');
    $this->expectExceptionMessage('Invalid event');

    $this->role->permission(new Obj());
  }

  public function testPermissionValueError() {
    $this->expectException('Error');
    $this->expectExceptionMessage('Permission panel.user.error of role testrole is invalid.');

    $this->role->permission('panel.user.error');
  }

  public function testPermissionCallbackError() {
    $this->expectException('Error');
    $this->expectExceptionMessage('Invalid user.');

    $this->role->permission('panel.user.update', 'someotheruser');
  }

  public function testPermissionCallbackReturnError() {
    $this->expectException('Error');
    $this->expectExceptionMessage('Permission panel.user.update of role testrole must return a boolean or error string.');

    $this->role->permission('panel.user.update', 'returnerror');
  }

  public function testCan() {
    $result = $this->role->can('testpermission');
    $this->assertTrue($result);

    $result = $this->role->can('panel.site.update');
    $this->assertFalse($result);

    $result = $this->role->can('panel.user.update', 'testuser');
    $this->assertTrue($result);

    $result = $this->role->can('panel.user.update', 'failuser1');
    $this->assertFalse($result);

    $result = $this->role->can('panel.user.update', 'failuser2');
    $this->assertFalse($result);
  }

  public function testCannot() {
    $result = $this->role->cannot('testpermission');
    $this->assertFalse($result);

    $result = $this->role->cannot('panel.site.update');
    $this->assertTrue($result);

    $result = $this->role->cannot('panel.user.update', 'testuser');
    $this->assertFalse($result);

    $result = $this->role->cannot('panel.user.update', 'failuser1');
    $this->assertTrue($result);

    $result = $this->role->cannot('panel.user.update', 'failuser2');
    $this->assertTrue($result);
  }
}
