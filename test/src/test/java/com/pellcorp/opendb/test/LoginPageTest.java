package com.pellcorp.opendb.test;

import com.pellcorp.opendb.test.LoginPage;
import com.pellcorp.opendb.test.RegistrationPage;
import com.pellcorp.opendb.test.WelcomePage;
import com.thoughtworks.selenium.Selenium;

import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.support.PageFactory;

import org.junit.After;
import org.junit.Assert;
import org.junit.Before;
import org.junit.Test;

public class LoginPageTest extends Assert {
    private LoginPage page;

    @Before
    public void openTheBrowser() {
        page = new LoginPage(new FirefoxDriver());
        page.open("http://localhost/opendb/login.php");
    }

    @After
    public void closeTheBrowser() {
        page.close();
    }

    @Test
    public void testDoRegister() throws Exception {
        RegistrationPage registerPage = page.register();
        assertEquals("New Account", registerPage.getHeaderTitle());
    }
    
    @Test
    public void doLogin() throws Exception {
        LoginPage result = page.loginWithFailure("admin", "password");
        assertEquals("Login failure", result.getError());
        
        WelcomePage welcome = page.login("admin", "admin");
    }
}
