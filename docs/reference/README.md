# Reference material

The two files in this folder (`UI_TESTING_TUTORIAL.md`, `SMOKE_TESTING_TUTORIAL.md`)
were written for a **different project** (a tutoring platform called Athlene --
tutor/student roles, session credits, Stripe checkout, file uploads). They're
kept here as reference because they explain the *underlying ideas* well
(explicit waits, disposable test databases, before/after state proof), but
none of their code, environment variables (`ATHLENE_DB_PATH`), or test
scenarios apply directly to this portfolio app.

For the tutorial that actually matches this codebase's test suite
(`tests/Unit`, `tests/Smoke`), see `../PHPUNIT_TUTORIAL.md` one level up.
