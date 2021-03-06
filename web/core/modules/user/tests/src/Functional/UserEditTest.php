<?php

namespace Drupal\Tests\user\Functional;

use Drupal;
use Drupal\Core\Cache\Cache;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests user edit page.
 *
 * @group user
 */
class UserEditTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test user edit page.
   */
  public function testUserEdit() {
    // Test user edit functionality.
    $user1 = $this->drupalCreateUser(['change own username']);
    $user2 = $this->drupalCreateUser([]);
    $this->drupalLogin($user1);

    // Test that error message appears when attempting to use a non-unique user name.
    $edit['name'] = $user2->getAccountName();
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t('The username %name is already taken.', ['%name' => $edit['name']]));

    // Check that the default value in user name field
    // is the raw value and not a formatted one.
    Drupal::state()->set('user_hooks_test_user_format_name_alter', TRUE);
    Drupal::service('module_installer')->install(['user_hooks_test']);
    Cache::invalidateTags(['rendered']);
    $this->drupalGet('user/' . $user1->id() . '/edit');
    $this->assertSession()->fieldValueEquals('name', $user1->getAccountName());

    // Ensure the formatted name is displayed when expected.
    $this->drupalGet('user/' . $user1->id());
    $this->assertSession()->responseContains($user1->getDisplayName());
    $this->assertSession()->titleEquals(strip_tags($user1->getDisplayName()) . ' | Drupal');

    // Check that filling out a single password field does not validate.
    $edit = [];
    $edit['pass[pass1]'] = '';
    $edit['pass[pass2]'] = $this->randomMachineName();
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertText("The specified passwords do not match.");

    $edit['pass[pass1]'] = $this->randomMachineName();
    $edit['pass[pass2]'] = '';
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertText("The specified passwords do not match.");

    // Test that the error message appears when attempting to change the mail or
    // pass without the current password.
    $edit = [];
    $edit['mail'] = $this->randomMachineName() . '@new.example.com';
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t("Your current password is missing or incorrect; it's required to change the %name.", ['%name' => t('Email')]));

    $edit['current_pass'] = $user1->passRaw;
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t("The changes have been saved."));

    // Test that the user must enter current password before changing passwords.
    $edit = [];
    $edit['pass[pass1]'] = $new_pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $new_pass;
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t("Your current password is missing or incorrect; it's required to change the %name.", ['%name' => t('Password')]));

    // Try again with the current password.
    $edit['current_pass'] = $user1->passRaw;
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t("The changes have been saved."));

    // Make sure the changed timestamp is updated.
    $this->assertEqual(REQUEST_TIME, $user1->getChangedTime(), 'Changing a user sets "changed" timestamp.');

    // Make sure the user can log in with their new password.
    $this->drupalLogout();
    $user1->passRaw = $new_pass;
    $this->drupalLogin($user1);
    $this->drupalLogout();

    // Test that the password strength indicator displays.
    $config = $this->config('user.settings');
    $this->drupalLogin($user1);

    $config->set('password_strength', TRUE)->save();
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t('Password strength:'));

    $config->set('password_strength', FALSE)->save();
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertNoRaw(t('Password strength:'));

    // Check that the user status field has the correct value and that it is
    // properly displayed.
    $admin_user = $this->drupalCreateUser(['administer users']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('user/' . $user1->id() . '/edit');
    $this->assertSession()->checkboxNotChecked('edit-status-0');
    $this->assertSession()->checkboxChecked('edit-status-1');

    $edit = ['status' => 0];
    $this->drupalPostForm('user/' . $user1->id() . '/edit', $edit, 'Save');
    $this->assertText('The changes have been saved.');
    $this->assertSession()->checkboxChecked('edit-status-0');
    $this->assertSession()->checkboxNotChecked('edit-status-1');

    $edit = ['status' => 1];
    $this->drupalPostForm('user/' . $user1->id() . '/edit', $edit, 'Save');
    $this->assertText('The changes have been saved.');
    $this->assertSession()->checkboxNotChecked('edit-status-0');
    $this->assertSession()->checkboxChecked('edit-status-1');
  }

  /**
   * Tests setting the password to "0".
   *
   * We discovered in https://www.drupal.org/node/2563751 that logging in with a
   * password that is literally "0" was not possible. This test ensures that
   * this regression can't happen again.
   */
  public function testUserWith0Password() {
    $admin = $this->drupalCreateUser(['administer users']);
    $this->drupalLogin($admin);
    // Create a regular user.
    $user1 = $this->drupalCreateUser([]);

    $edit = ['pass[pass1]' => '0', 'pass[pass2]' => '0'];
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, 'Save');
    $this->assertRaw(t("The changes have been saved."));
  }

  /**
   * Tests editing of a user account without an email address.
   */
  public function testUserWithoutEmailEdit() {
    // Test that an admin can edit users without an email address.
    $admin = $this->drupalCreateUser(['administer users']);
    $this->drupalLogin($admin);
    // Create a regular user.
    $user1 = $this->drupalCreateUser([]);
    // This user has no email address.
    $user1->mail = '';
    $user1->save();
    $this->drupalPostForm("user/" . $user1->id() . "/edit", ['mail' => ''], 'Save');
    $this->assertRaw(t("The changes have been saved."));
  }

  /**
   * Tests well known change password route redirects to user edit form.
   */
  public function testUserWellKnownChangePasswordAuth() {
    $account = $this->drupalCreateUser([]);
    $this->drupalLogin($account);
    $this->drupalGet('.well-known/change-password');
    $this->assertSession()->addressEquals("user/" . $account->id() . "/edit");
  }

  /**
   * Tests well known change password route returns 403 to anonymous user.
   */
  public function testUserWellKnownChangePasswordAnon() {
    $this->drupalGet('.well-known/change-password');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests that a user is able to change site language.
   */
  public function testUserChangeSiteLanguage() {
    // Install these modules here as these aren't needed for other test methods.
    Drupal::service('module_installer')->install([
      'content_translation',
      'language',
    ]);
    // Create and login as an admin user to add a new language and enable
    // translation for user accounts.
    $adminUser = $this->drupalCreateUser([
      'administer account settings',
      'administer languages',
      'administer content translation',
      'administer users',
      'translate any entity',
    ]);
    $this->drupalLogin($adminUser);

    // Add a new language into the system.
    $edit = [
      'predefined_langcode' => 'fr',
    ];
    $this->drupalPostForm('admin/config/regional/language/add', $edit, 'Add language');
    $this->assertSession()->pageTextContains('French');

    // Enable translation for user accounts.
    $edit = [
      'language[content_translation]' => 1,
    ];
    $this->drupalPostForm('admin/config/people/accounts', $edit, 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // Create a regular user for whom translation will be enabled.
    $webUser = $this->drupalCreateUser();

    // Create a translation for a regular user account.
    $this->drupalPostForm('user/' . $webUser->id() . '/translations/add/en/fr', [], 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');

    // Update the site language of the user account.
    $edit = [
      'preferred_langcode' => 'fr',
    ];
    $this->drupalPostForm('user/' . $webUser->id() . '/edit', $edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
  }

}
