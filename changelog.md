---
layout: default
permalink: /changelog/
title: Changelog
---

# Changelog

All notable changes to Monga will be documented in this file.

{% for release in site.github.releases %}   
## {{ release.name }}
{{ release.body | markdownify }}
{% endfor %}
