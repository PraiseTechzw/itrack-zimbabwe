---
name: Feature request
title: "[FEATURE] "
description: Suggest a new feature or enhancement
labels: enhancement
body:
  - type: textarea
    id: summary
    attributes:
      label: Summary
      description: Describe the proposed feature.
      placeholder: Add a new reporting module...
    validations:
      required: true
  - type: textarea
    id: motivation
    attributes:
      label: Motivation
      description: Why is this feature useful?
    validations:
      required: true
