package com.pellcorp.opendb.login;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;

public class LoginPage {
    private WebDriver driver;

    @FindBy(name = "uid")
    WebElement userIdField;

    @FindBy(name = "passwd")
    WebElement passwordField;

    @FindBy(how = How.XPATH, using = "//input[@type='submit']")
    WebElement loginButton;
    
    @FindBy(how = How.XPATH, using = "//p[@class='error']")
    WebElement errorText;
    
    public LoginPage(WebDriver driver) {
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
        return PageFactory.initElements(driver, LoginPage.class);
    }

    public WelcomePage login(String userId, String password) {
        doLogin(userId, password);
        return PageFactory.initElements(driver, WelcomePage.class);
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
