/* XMB Application Variables - These are likely to be needed for backward and forward theme compatibility */

.admin-attachment-result {
    --xmb-grid-columns: auto 20% 28% 11% max-content max-content;
}
@media screen and (max-width: 850px) {
    .admin-attachment-result {
        --xmb-grid-columns: auto 20% 28% max-content;
    }
}
.admin-attachment-search {
    --xmb-grid-columns: max-content auto;
}
.admin-censor {
    --xmb-grid-columns: auto auto auto;
}
.admin-email,
.admin-forums-detail,
.admin-moderators,
.admin-newsletter,
.admin-settings {
    --xmb-grid-columns: auto auto;
}
.admin-search-result {
    --xmb-grid-columns: 5% auto;
}

.admin-attachment-result .span {
    --xmb-grid-span: 1 / span 6;
}
@media screen and (max-width: 850px) {
    .admin-attachment-result .cell:nth-child(4),
    .admin-attachment-result .cell:nth-child(6) {
        --xmb-column-hide: none;
    }
    .admin-attachment-result .span,
    .admin-attachment-result .category-head {
        --xmb-grid-span: 1 / span 4;
    }
}
.admin-censor .span {
    --xmb-grid-span: 1 / span 3;
}
.admin-attachment-search .span,
.admin-email .span,
.admin-forums-detail .span,
.admin-moderators .span,
.admin-newsletter .span,
.admin-search-result .span,
.admin-settings .span {
    --xmb-grid-span: 1 / span 2;
}
