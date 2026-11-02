## Summary

<!-- What changed, and why is the change needed? -->

## Scope

<!-- List the affected modules, routes, APIs, views, services, or database tables. -->

## Testing

- [ ] PHP syntax check completed: `find app api public includes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l`
- [ ] Relevant automated or helper checks completed
- [ ] Manual smoke test completed for the affected workflow
- [ ] Authentication, authorization, validation, and error paths checked where relevant
- [ ] API responses checked where relevant
- [ ] Desktop and mobile screens checked where relevant

## Database and deployment

- [ ] No database change
- [ ] Schema/seed/migration updated and tested
- [ ] Deployment or environment-variable instructions updated
- [ ] Backward compatibility and rollback considerations documented

## Documentation and screenshots

- [ ] README or the relevant guide updated
- [ ] Screenshot index updated for visible UI changes
- [ ] New screenshots contain no production or personal data
- [ ] Changelog entry added when the change is release-worthy

## Security and data handling

- [ ] No secrets, credentials, tokens, dumps, or private uploads are included
- [ ] CSRF, input validation, output escaping, and permission checks preserved
- [ ] Security-sensitive changes are called out for reviewer attention

## Notes for reviewers

<!-- Mention risks, known limitations, follow-up work, or rollout steps. -->
