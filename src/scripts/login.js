var captchaToken = "";

var regBTN = document.getElementById("registerBtn");
var loginBTN = document.getElementById("loginBtn");
var regForm = document.getElementById("registerForm");
var loginForm = document.getElementById("loginForm");
var regUsername = document.getElementById("regUsername");
var regPassword = document.getElementById("regPassword");
var regEmail = document.getElementById("regEmail");
var loginUsername = document.getElementById("loginUsername");
var loginPassword = document.getElementById("loginPassword");
var captchaElement = document.getElementById("hcaptcha");
var forgetBTN = document.getElementById("forget");

function onCaptchaSuccess(token) {
    captchaToken = token;

    regBTN.disabled = false;

    console.log('hCaptcha token:', captchaToken);
}

function resetCaptcha() {
    regBTN.disabled = true;
    captchaToken = "";

    hcaptcha.reset();
}

function md5(str) {
    return CryptoJS.MD5(str).toString();
}


regBTN.addEventListener("click", function() {
    if (regUsername.value && regPassword.value && regEmail.value && captchaToken) {
        const fd = new FormData();
        fd.append("username", regUsername.value);
        fd.append("password", regPassword.value);
        fd.append("email", regEmail.value);
        fd.append("hcaptcha", captchaToken);

        fetch("../api/sys/register.php", {
            method: "POST",
            body: fd
        }).then(res => res.json()).then(data => {
            resetCaptcha();
            if (data.success) {
                if (data.message && data.message == "Verification email sent") {
                    alert("Письмо с подтверждением отправлено на почту!\nЕсли письма долго нет, то проверьте спам");
                } else {
                    alert("Вы успешно зарегистрировались!");
                    window.location.href = "../";
                }
            } else {
                alert(data.error);
            }
        });
    }
});


loginBTN.addEventListener("click", function() {
    if (loginUsername.value && loginPassword.value) {
        const fd = new FormData();
        fd.append("username", loginUsername.value);
        fd.append("password", loginPassword.value);

        fetch("../api/sys/login.php", {
            method: "POST",
            body: fd
        }).then(res => res.json()).then(data => {
            resetCaptcha();
            if (data.success) {
                if (document.getElementById("rememberMe").checked) {
                    const encodedCredentials = btoa(`${loginUsername.value}:${md5(loginPassword.value)}`); // Конвертируем в base64
                    const expirationDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
                    document.cookie = `LongLifeToken=${encodedCredentials}; expires=${expirationDate.toUTCString()}; path=/;`;
                }

                alert("Вы успешно вошли!" + (document.getElementById("rememberMe").checked ? "\nДанные сохранены в куки" : ""));
                window.location.href = "../";
            } else {
                alert(data.error);
            }
        });
    }
});


function handleMessageInParams() {
    const params = new URLSearchParams(window.location.search);
    if (params.has("msg")) {
        document.getElementById("notificationText").textContent = params.get("msg");

        // play animation
        const ntfc = document.getElementById("notification")
        ntfc.classList.toggle("show");
        ntfc.classList.toggle("hide");

        const url = new URL(window.location.href);
        url.searchParams.delete("msg");
        window.history.replaceState(null, null, url.href);

        setTimeout(() => {
            ntfc.classList.toggle("show");
            ntfc.classList.toggle("hide");
        }, 15000);
    }
}


function restore() {
    console.log(document.getElementById("restore-email").value);
    const fd = new FormData();
    fd.append("email", document.getElementById("restore-email").value);

    fetch("../api/sys/forget.php", {
        method: "POST",
        body: fd
    }).then(res => res.json()).then(data => {
        if (data.success) {
            alert("Письмо с подтверждением отправлено на почту!\nЕсли письма долго нет, то проверьте спам");
            hideModal(document.getElementById("forget-modal"));
        } else {
            alert(data.error);
        }
    });
}

forgetBTN.addEventListener("click", function() {
    const tempModalDiv = document.createElement("div");

    const modal_input = document.createElement("input");
    modal_input.type = "email";
    modal_input.id = `restore-email`
    modal_input.placeholder = "Почта, на которую зарегестрирован аккаунт";
    tempModalDiv.appendChild(modal_input);

    const modal_button = document.createElement("button");
    modal_button.textContent = "Восстановить";
    modal_button.setAttribute("onclick", "restore()")

    tempModalDiv.appendChild(modal_button);

    showModal("Восстановление доступа", tempModalDiv.innerHTML, { closeButton: true }, "forget-modal");
});

async function init() {
    handleMessageInParams();

    let settings = await fetch("../api/config.json");
    settings = await settings.json();

    captchaElement.setAttribute("data-sitekey", settings.captcha.sitekey);

    var hcaptchaApi = document.createElement("script");
    hcaptchaApi.src = "https://hcaptcha.com/1/api.js";
    hcaptchaApi.async = true;
    hcaptchaApi.defer = true;
    document.head.appendChild(hcaptchaApi);
}

init();