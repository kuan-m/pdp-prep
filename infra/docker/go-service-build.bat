@echo off
cd services\go-service
set CGO_ENABLED=0
set GOOS=linux
set GOARCH=amd64
go build -o ..\..\build\go-service .\cmd\main.go

