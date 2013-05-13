package com.pellcorp.opendb.login;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class LoginPage {
    private WebDriver driver;

    public LoginPage(WebDriver driver) {
        this.driver = driver;
    }

    public void open(String url) {
        driver.get(url);
    }

    public void close() {
        driver.quit();
    }
    
    /**
     * Return error string if it is found
     * @param userId
     * @param password
     * @return
     */
    public String doLogin(String userId, String password) {
        WebElement userIdField = driver.findElement(By.id("uid"));
        userIdField.click();
        userIdField.clear();
        userIdField.sendKeys(userId);
        
        WebElement passwordField = driver.findElement(By.id("passwd"));
        passwordField.click();
        passwordField.clear();
        passwordField.sendKeys(password);
        
        WebElement loginButton = driver.findElement(By.xpath("//input[@type='submit']"));
        loginButton.click();

        try {
            WebElement errorText = driver.findElement(By.xpath("//p[@class='error']"));
            return errorText.getText();
        } catch (NoSuchElementException nse) {
            return null;
        }
    }
}
