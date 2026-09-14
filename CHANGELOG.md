# Changelog

All notable changes to `LaraFiles` will be documented in this file.

### Version 2.3.0

- the `url` attribute of a private file on a disk that can sign URLs (s3, DigitalOcean Spaces) now returns a temporary
  URL instead of a plain URL the storage rejects
- on s3-compatible disks, temporary URLs returned by `url` keep the file's original name through an inline
  `Content-Disposition`
- added `temporary_url_expiration` config option (minutes, default 120, capped at 10080)

### Version 2.0.0

- Completely rewritten code
- uploadHttpFile() and uploadBase64File() methods now return LaraFile model and require less parameters
- uploadHttpFiles() and uploadBase64Files() methods now return Collection of LaraFile models
- added addHttpFile() and addBase64File() methods which return service object which you can use to customize the upload
  process
- added file ordering system
- added support for custom properties
- added ability to copy file from another LaraFile model
- added option to change disk easily
- added properties in LaraFile model like size, mime type, last modified, etc.
- added ability to download file directly from LaraFile model using download() method
- added ability to delete file directly from LaraFile model using delete() method
- added option to store file without model to attach as tmp file

### Version 1.0.0

###### Added

- Everything