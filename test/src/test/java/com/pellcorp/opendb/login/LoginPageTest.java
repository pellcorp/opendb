package com.pellcorp.opendb.login;

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
        page = PageFactory.initElements(new FirefoxDriver(), LoginPage.class);
        page.open("http://localhost/opendb/login.php");
    }

    @After
    public void closeTheBrowser() {
        page.close();
    }

    @Test
    public void doLogin() throws Exception {
        String result = page.doLogin("admin", "password");
        assertEquals("Login failure", result);
        
        result = page.doLogin("admin", "admin");
        assertNull(result);
    }
}
