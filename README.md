Ratree Samosorn
=========

#User API
User API

##GET /me
Get user info by "access_token".
```
{
    "access_token": "f7d99d04cffd57acd72676afb6603fdb"
}
```

##GET /register
Register user
```
{
    "username": string(4-32 character),
    "password": string(4-16 character),
    "email": string(email format),
    "gender": string(in male, female),
    "birth_date": date format(Y-m-d example 1990-10-30);
}
```

#OAuth API
Api Authorize