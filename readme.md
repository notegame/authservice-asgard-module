# AuthService Module

# API DOC

## Guest Only

### `POST` /api/auth/login

Attribute             | Type     | Example |
--------------------- | -------- | --------
login                 | `String`
password              | `String`
device_id             | `String`
device_os             | `String` | **example:** android, ios, windows

### `POST` /api/auth/loginByToken

Attribute             | Type     | Example |
--------------------- | -------- | --------
token                 | `String`
device_id             | `String`
device_os             | `String` | **example:** android, ios, windows

### `POST` /api/auth/register

Attribute             | Type     |
--------------------- | -------- |
login                 | `String`
password              | `String`
password_confirmation | `String`

### `POST` /api/auth/activation

Attribute             | Type     
--------------------- | -------- 
ref_id                | `String`
code                  | `String`

### `POST` /api/auth/forget_password

Attribute             | Type     
--------------------- | -------- 
email                 | `String`

## Auth Only

### `POST` /api/auth/change_password

Attribute             | Type     
--------------------- | -------- 
current_password      | `String`
password              | `String`

### `POST` /api/auth/update

Attribute             | Type     
--------------------- | -------- 
email                 | `String`
first_name            | `String`
last_name             | `String`
and another...        | 

### `GET` /api/auth/logout

# Developer Guide
## Events
- UserActivationWasCreated
- UserWasChangedPassword
- UserWasActivated

## Middleware
- auth.token : TokenAuthenticate

