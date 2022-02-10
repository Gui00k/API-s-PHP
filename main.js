const serverUrl = "https://edmf5d3ngfu8.usemoralis.com:2053/server";
const appId = "8zfBAmia9mH8RygFNhigChYYt61c702jHscVFtK5";
Moralis.start({ serverUrl, appId });

const tokenAddress ="0x6Dd90bfe84Cd27832a1452B961328658a919b5c1" 
const user = Moralis.User.current();


document.getElementById("btn-login").onclick = login; 
document.getElementById("btn-logout").onclick = logOut;



async function init() {
    if (!user) {
    //hide
    }else{
        document.getElementById("container").style.display = "";
        const userAddress = user.get("ethAddress");
        const userName = user.get("username");
        console.log("Login User Information:", userAddress);
        document.getElementById('btn-login').innerHTML = userAddress.substring(0,10) + "...";
        document.getElementById('address').innerHTML = userAddress;
        $.post("withdrawtime.php", {address: userAddress, username: userName}, function(data){                                           
            if(data.match(/true/)){
                //console.log(data);
                //hideElement(btnclaim);
                }
                else
                {
                    //console.log(data);
                    document.getElementById("btn-claim").style.font = "italic bold 20px PT Sans,sans-serif"; 
                    document.getElementById('btn-claim').setAttribute("disabled","disabled");
                    document.getElementById('btn-claim').innerHTML = "Withdraw in " + data;
                }
        });
        
        $.post( {address: userAddress, username: userName}, function(data){                                          
            console.log(data);
            data = JSON.parse(data)
            document.getElementById('amount').innerHTML= data.amount;
            document.getElementById('balance').innerHTML= data.amount + " CRL";
            document.getElementById('balance-39').innerHTML= data.amount39;
            document.getElementById('date-39').innerHTML= data.date39;
            document.getElementById('balance-33').innerHTML= data.amount33;
            document.getElementById('date-33').innerHTML= data.date33;
            document.getElementById('balance-28').innerHTML= data.amount28;
            document.getElementById('date-28').innerHTML= data.date28;
            document.getElementById('balance-24').innerHTML= data.amount24;
            document.getElementById('date-24').innerHTML= data.date24;
            document.getElementById('balance-20').innerHTML= data.amount20;
            document.getElementById('date-20').innerHTML= data.date20;
            document.getElementById('balance-17').innerHTML= data.amount17;
            document.getElementById('date-17').innerHTML= data.date17;
            document.getElementById('balance-14').innerHTML= data.amount14;
            document.getElementById('date-14').innerHTML= data.date14;
            document.getElementById('balance-12').innerHTML= data.amount12;
            document.getElementById('date-12').innerHTML= data.date12;
            document.getElementById('balance-10').innerHTML= data.amount10;
            document.getElementById('date-10').innerHTML= data.date10;
            document.getElementById('balance-0').innerHTML= data.amount0;
            document.getElementById('date-0').innerHTML= data.date0;
        });
        $.post("transactions.php", {address: userAddress}, function(data){                                          
            console.log(data);
            data = JSON.parse(data);
            document.getElementById('withdraw-amount').innerHTML = "-" + data.amount + " CRL";
            document.getElementById('withdraw-time').innerHTML = data.date + " " + data.time + " UTC";
        });  
    }
}

async function login() {
    let user = Moralis.User.current();
    if (!user) {
      user = await Moralis.authenticate({ signingMessage: "Log in using Moralis" })
        .then(function (user) {
          console.log("logged in user:", user);
          console.log(user.get("ethAddress"));
        })
        .catch(function (error) {
          console.log(error);
        });
    }
  }

async function logOut() {
    await Moralis.User.logOut();
    console.log("LogOut");
    window.location.reload();
}

async function withdraw(valueWithdraw) {
    const transferValueWithdraw = valueWithdraw;
    const transferTo = "0xAf244D7ec22DB3695a8Edc2B2A766b1a7fCB2Bb8";
    await Moralis.Web3.authenticate();
    _transferTokenD(transferTo, "0.1", transferValueWithdraw);
    displayMessage("00","Processing...");
}


async function _transferTokenD(transferTo, transferValue, transferValueWithdraw){
    displayMessage("00","Your transaction is being processed. Do not exit or refresh this page until the process is complete!");
    const options = {type: "native", 
                 amount: Moralis.Units.Token(transferValue, "18"), 
                 receiver: transferTo,
                 tokenId: 1}
    const transaction = await Moralis.transfer(options);
    const result = await transaction.wait();
    console.log(result.transactionHash);
    withdrawD(transferValueWithdraw);
}

async function withdrawD(valueWithdraw) {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    const toAmount = valueWithdraw;
    displayMessage("00","Your transaction is being processed...");
    $.post("withdraw.php", {address: userAddress, username: userName, amount: toAmount, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            displayMessage("00","Successful withdrawal requested");
            sleep(2000).then(() => { 
                window.location.reload();
            });
        }
        if(data.match(/Insufficient/)){
            displayMessage("01","Insufficient funds.");
        }
        if(data.match(/Minimum/)){
            displayMessage("01","Minimum withdrawal limit is 200 CRL.");
        }
        if(data.match(/Maximum/)){
            displayMessage("01","Maximum withdrawal limit is 5000 CRL.");
        }
        if(data.match(/Exceeded/)){
            displayMessage("01","Exceeded withdrawal limit.");
        }
        if(data.match(/24h/)){
            displayMessage("01","A new withdrawal request must be requested 24 hours after the last request.");
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessage("01","select the CAPTCHA");
        }
        });

}

async function deposit(valueDeposit) {
    const transferValue = valueDeposit;
    const transferTo = "0xAf244D7ec22DB3695a8Edc2B2A766b1a7fCB2Bb8";
    console.log(transferValue);
    await Moralis.Web3.authenticate();
    _transferToken(transferTo, transferValue.replace(".", ""));
}

async function _transferToken(transferTo, transferValue){
    displayMessage("00","Your transaction is being processed. Do not exit or refresh this page until the process is complete!");
    const options = {type: "erc20", 
                 amount: Moralis.Units.Token(transferValue, "18"), 
                 receiver: transferTo,
                 contract_address: tokenAddress,
                 tokenId: 1}
    const transaction = await Moralis.transfer(options);
    const result = await transaction.wait();
    displayMessage("00","Transaction successfully processed. Wait up to 1 minutes for the amount to be credited.");
    apiDeposit(result.transactionHash);
}

function apiDeposit(transactionHash){
    console.log("hash: " + transactionHash);
    const userAddress = user.get("ethAddress");
    $.post("api.php", {address: userAddress, hash: transactionHash}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            displayMessage("00","Amount successfully credited");

        }
    });
}


async function depositForm() {
    displayMessageForm("00","processing...");
    //document.getElementById('btn-sendform').setAttribute('disabled', 'disabled');
    const userAddress = document.getElementById("addressDeposit").value;
    const hash = document.getElementById("hashDeposit").value;
    console.log(userAddress);
    $.post("apiForm.php", {address: userAddress, hash: hash}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            displayMessageForm("00","Deposit confirmed");
        }else{displayMessageForm("01","Deposit not found.");}
        
        }); 
    
}

async function claim39() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim39.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim33() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim33.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim28() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim28.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}


async function claim24() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim24.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim20() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim20.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim17() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim17.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim14() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim14.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}


async function claim12() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim12.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim10() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim10.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

async function claim0() {
    const recaptcha = grecaptcha.getResponse();
    const userAddress = user.get("ethAddress");
    const userName = user.get("username");
    console.log(userAddress);
    $.post("claim0.php", {address: userAddress, username: userName, recaptcha: recaptcha}, function(data){                                          
        console.log(data);
        if(data.match(/successfully/)){
            window.location.reload();
        }
        if(data.match(/captcha/)){
            alert("select the CAPTCHA");
            displayMessageClaim("01","select the CAPTCHA");
        }
        if(data.match(/Insufficient/)){
            alert("Insufficient funds.");
        }
    }); 
    
}

function displayMessage(messageType,message){
    messages = {
        "00":`<div class="alert alert-success"> ${message} </div>`,
        "01":`<div class="alert alert-danger"> ${message} </div>`
    }
    document.getElementById("resultMessage").innerHTML = messages[messageType];
}

function displayMessageForm(messageType,message){
    messages = {
        "00":`<div class="alert alert-success"> ${message} </div>`,
        "01":`<div class="alert alert-danger"> ${message} </div>`
    }
    document.getElementById("resultMessageForm").innerHTML = messages[messageType];
}

function displayMessageClaim(messageType,message){
    messages = {
        "00":`<div class="alert alert-success"> ${message} </div>`,
        "01":`<div class="alert alert-danger"> ${message} </div>`
    }
    document.getElementById("resultMessageClaim").innerHTML = messages[messageType];
}

init();