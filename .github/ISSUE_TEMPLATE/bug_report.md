---

name: Bug report
title: "[BUG] "
description: Report a problem with the application
labels: bug
body:

- type: textarea
  id: description
  attributes:
  label: Description
  description: Describe the issue clearly.
  placeholder: What happened?
  validations:
  required: true
- type: textarea
  id: steps
  attributes:
  label: Steps to reproduce
  description: Provide the steps to reproduce the problem.
  placeholder: 1. Open the app...
  validations:
  required: true
- type: textarea
  id: expected
  attributes:
  label: Expected behavior
  description: What should happen instead?
  validations:
  required: true
- type: textarea
  id: environment
  attributes:
  label: Environment
  description: Include PHP version, browser, and operating system if relevant.
