package com.pellcorp.opendb.test;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;

public class LoginPage {
    private WebDriver driver;

    @FindBy(name = "uid")
    private WebElement userIdField;

    @FindBy(name = "passwd")
    private WebElement passwordField;

    @FindBy(how = How.XPATH, using = "//input[@type='submit']")
    private WebElement loginButton;
    
    @FindBy(how = How.XPATH, using = "//p[@class='error']")
    private WebElement errorText;
    
    @FindBy(how = How.LINK_TEXT, using = "Sign me up!")
    private WebElement registerLink;
    
    public LoginPage(WebDriver driver) {
        PageFactory.initElements(driver, this);
        this.driver = driver;
    }

    public void open(String url) {
        driver.get(url);
    }

    public void close() {
        driver.close();
    }

    public LoginPage loginWithFailure(String userId, String password) {
        doLogin(userId, password);
        return new LoginPage(driver);
    }

    public RegistrationPage register() {
        registerLink.click();
        driver.getTitle(); // not sure it's needed but it does seem to fix the issue
        return new RegistrationPage(driver);
    }
    
    public WelcomePage login(String userId, String password) {
        doLogin(userId, password);
        return new WelcomePage(driver);
    }

    public String getError() {
        return errorText.getText();
    }
    
    private void doLogin(String userId, String password) {
        userIdField.click();
        userIdField.clear();
        userIdField.sendKeys(userId);

        passwordField.click();
        passwordField.clear();
        passwordField.sendKeys(password);

        loginButton.click();
    }
}
