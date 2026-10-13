**Saving a page no longer eats its content.** Editing a CMS page in the admin used to write a
draft built only from the fields the block form declares. Everything else — terminal lines,
comparison columns and cells, footnotes, table captions — was dropped, and the page rendered
broken until the data was restored by hand. The draft is now merged with the published blocks, so
whatever the form does not touch is kept as it was.

**The `pricing` block stores its features properly.** Its `features` field wrote a bare list of
strings where the views and the seeded data expected `feature` objects. It now matches.

### Tests

A guard test now photographs every key of every published block, performs a real admin save, and
fails if the resulting draft lost anything. On the marketing site it caught 196 lost keys on the
homepage and 10 on the sponsors page before this fix; both are at zero now.
