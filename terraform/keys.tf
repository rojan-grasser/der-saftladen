output "s3_api_access_key" {
    value     = minio_iam_service_account.s3_api_keys.access_key
    sensitive = true
}

output "s3_api_secret_key" {
    value     = minio_iam_service_account.s3_api_keys.secret_key
    sensitive = true
}
