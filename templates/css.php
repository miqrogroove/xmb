/* XMB Stylesheet */
body {
    scrollbar-arrow-color: <?= $THEME['header'] ?>;
    scrollbar-base-color: <?= $THEME['altbg1'] ?>;
    text-align: left;
    <?= $THEME['bgcode'], "\n" ?>
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
}

body > br {
    line-height: 18px;
}

a {
    color: <?= $THEME['link'] ?>;
    text-decoration: none;
}

a:hover, .category a:hover {
    text-decoration: underline;
}

a.button-link {
    display: inline-block;
    padding: 1px 4px;
}

.category a {
    color: <?= $THEME['cattext'] ?>;
    text-decoration: none;
}

form {
    margin: 0px;
}

hr {
    color: <?= $THEME['bordercolor'] ?>;
    background-color: <?= $THEME['bordercolor'] ?>;
    border: 0px;
    height: 1px;
}

#tickertoggle {
    color: <?= $THEME['cattext'] ?>;
}

table.code {
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    margin: 15px auto 10px auto;
}

td.code {
    background-color: <?= $THEME['header'] ?>;
    border-bottom: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-weight: bold;
    height: 20px;
    padding-left: 5px;
}

code {
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    padding: 1px 4px;
}

.codemessage code {
    background-color: <?= $THEME['altbg2'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    padding: 10px 5px 10px 5px;
    white-space: pre;
    display: block;
    width: 600px;
    max-height: 400px;
    overflow: auto;
    border: none;
}

table.quote {
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    margin: 15px 5% 10px 5%;
    width: 80%;
}

td.quote {
    background-color: <?= $THEME['header'] ?>;
    border-bottom: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-weight: bold;
    height: 20px;
    padding-left: 5px;
}

td.quotemessage {
    background-color: <?= $THEME['altbg2'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    padding: 10px 0 20px 5px;
}

textarea, select, input, object, a.button-link {
    background-color: <?= $THEME['altbg1'] ?>;
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    font-family: Verdana, arial, helvetica, sans-serif;
    font-size: <?= $THEME['font-larger-1'] ?>;
    font-weight: normal;
    table-layout: fixed;
}

input[readonly] {
    background: none;
    border: none;
}

.category {
    <?= $THEME['catcss'] ?>
    table-layout: fixed;
    text-align: left;
}

.ctrcategory {
    <?= $THEME['catcss'] ?>
    table-layout: fixed;
    text-align: center;
}

.ctrtablerow {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
    text-align: center;
}

.header2 {
    background-color: <?= $THEME['altbg1'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.mediumtxt {
    color: <?= $THEME['tabletext'] ?>;
    font-weight: normal;
    table-layout: fixed;
}

.multi {
    table-layout: fixed;
}

.nav {
    font-weight: bold;
    table-layout: fixed;
}

.navtd {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    table-layout: fixed;
    text-decoration: none;
}

.navtd a {
    white-space: nowrap;
}

.navtd2 {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-size: <?= $THEME['font-smaller-2'] ?>;
    table-layout: fixed;
    text-decoration: none;
}

.post {
    font-size: <?= $THEME['font-larger-2'] ?>;
    font-weight: normal;
}

.rghttablerow {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
    text-align: right;
}

.lastpostcell {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
    text-align: right;
    padding: 0;
}

.lastpostcell img {
    padding-left: 0.5em;
    padding-right: 0.5em;
}

.tablelinks .lastpostcell a {
    padding-top: 1.5em;
    padding-bottom: 1.5em;
}

.sig {
    border-top: 1px dashed <?= $THEME['bordercolor'] ?>;
    padding: 13px 0 0 3px;
}

.smalltxt {
    font-size: <?= $THEME['font-smaller-1'] ?>;
    table-layout: fixed;
}

.subject {
    font-weight: bold;
}

.submit {
    text-align: center;
}

.tablerow {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
}

span[class^="status_"] {
    color: <?= $THEME['tabletext'] ?>;
    font-size: <?= $THEME['font-smaller-1'] ?>;
}

.status_Super_Administrator {
    text-decoration: underline;
    font-weight: bold;
    font-style: italic;
}

.status_Administrator {
    text-decoration: underline;
    font-weight: bold;
}

.status_Super_Moderator {
    text-decoration: none;
    font-weight: bold;
    font-style: italic;
}

.status_Moderator {
    text-decoration: none;
    font-weight: bold;
}

.status_Member, .status_Banned {
    text-decoration: none;
    font-weight: normal;
}

.tablelinks td {
    overflow: hidden;
}
.tablelinks td a {
    display: block;
    width: 100%;
    margin: -1em;
    padding: 1em;
}
.tablelinks .plainlinks a, .tablelinks .multi a {
    display: inline;
    width: unset;
    margin: 0;
    padding: 0;
}

table.subforums {
    margin-bottom: 12px;    
}

.video {
    border: none;
    height: 344px;
    width: 600px;
}

.postOptions label {
    display: block;
}

.quickreply .inputWrap {
    display: flex;
}

.quickreply textarea {
    width: 80%;
}

.quickreply .controls {
    margin-left: 8px;
}

.medium-width-box {
    min-width: 50%;
}

@media screen and (min-width: 1200px) {
    .codemessage code {
        width: 800px;
    }
}
@media screen and (min-width: 1500px) {
    .codemessage code {
        width: 1100px;
    }
}
@media screen and (min-width: 1850px) {
    .codemessage code {
        width: 1400px;
    }
    .medium-width-box {
        min-width: 925px;
        max-width: 1000px;
    }
}
@media screen and (max-width: 850px) {
    .video {
        height: 300px;
        width: 425px;
    }
    .codemessage code {
        width: 425px;
    }
}
@media screen and (max-width: 600px) {
    .video {
        height: 300px;
        width: 350px;
    }
    .codemessage code {
        width: 350px;
    }
    .quickreply .inputWrap {
        display: block;
    }
    .quickreply textarea {
        width: 99%;
    }
}

.blink {
    animation: blinker 1.5s linear infinite;
}

@keyframes blinker {
    50% {
        opacity: 0;
    }
}

.marquee {
    max-width: 100%;
    overflow: hidden;
}

.marquee2 {
    display: flex;
    align-items: center;
    flex-grow: 0;
    flex-shrink: 0;
    margin: 0;
    padding: 0 2rem;
    list-style: none;
    transform: translateX(100%);
    animation: marqueeAnimation 20s linear infinite;
}

@keyframes marqueeAnimation {
    0% {
        transform: translateX(100%);
    }

    100% {
        transform: translateX(-100%);
    }
}

#tabs {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 5px;
    border: 1px solid #ccc;
    background-color: <?= $THEME['altbg2'] ?>;
}

#tabs button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
}

#tabs button:hover {
    background-color: <?= $THEME['altbg1'] ?>;
}

#tabs button.active {
    background-color: <?= $THEME['header'] ?>;
}

table.fdetails {
    max-width: 1024px;
}

/* Application Variables - These are likely to be neither predictable nor to require any customization */

.admin-attachment-result {
    --xmb-grid-columns: auto 20% 28% 11% max-content max-content;
}
.admin-attachment-search {
    --xmb-grid-columns: max-content auto;
}
.admin-censor {
    --xmb-grid-columns: auto auto auto;
}
.admin-email,
.admin-newsletter,
.admin-settings {
    --xmb-grid-columns: auto auto;
}
.admin-search-result {
    --xmb-grid-columns: 5% auto;
}

/* Table Replacement Styles */

.xmb-grid {
    margin: <?= $THEME['borderwidth'] ?> auto;
    display: grid;
    grid-template-columns: var(--xmb-grid-columns);
}

.xmb-tabbed-block-form,
.admin-settings .group {
    margin: <?= $THEME['borderwidth'] ?> auto;
    display: none;
    grid-template-columns: var(--xmb-grid-columns);
}

.xmb-block-simple,
.admin-block,
.admin-search-form {
    margin: <?= $THEME['borderwidth'] ?> auto;
}

.admin-settings div#boardDetail {
    display: grid;
}

.xmb-block-narrow-450, .admin-censor {
    max-width: 450px;
}

.xmb-block-narrow-550,
.admin-attachment-search,
.admin-search-form {
    max-width: 550px;
}

.xmb-block-medium-750, .admin-newsletter {
    max-width: 750px;
}

.xmb-block-medium-800, .admin-settings .group {
    max-width: 800px;
}

.xmb-block-wide-93, .admin-attachment-result {
    width: 93%;
}

.xmb-block-wide-default,
.admin-block,
.admin-email,
.admin-search-result {
    width: calc(<?= $THEME['tablewidth'] ?> - (2 * <?= $THEME['borderwidth'] ?>));
}

.xmb-block-98-flex, .admin-panel {
    width: calc(98% - (2 * <?= $THEME['borderwidth'] ?>));
    margin: <?= $THEME['borderwidth'] ?> auto;
    display: flex;
    flex-wrap: wrap;
}

.xmb-content-row {
    padding: <?= $THEME['tablespace'] ?>;
}

.admin-attachment-wrap,
.admin-block-wrap,
.admin-censor-wrap,
.admin-email-wrap,
.admin-newsletter-wrap,
.admin-panel-wrap,
.admin-search-wrap {
    margin-top: -<?= $THEME['borderwidth'] ?>;
    margin-left: -<?= $THEME['borderwidth'] ?>;
}

.admin-settings {
    margin-top: calc(-<?= $THEME['borderwidth'] ?> + <?= $THEME['tablespace'] ?> + 1px);
    margin-left: -<?= $THEME['borderwidth'] ?>;
}

.xmb-flex-4, .admin-panel .group {
    border: <?= $THEME['borderwidth'] ?> solid <?= $THEME['bordercolor'] ?>;
    margin-right: -<?= $THEME['borderwidth'] ?>;
    margin-bottom: -<?= $THEME['borderwidth'] ?>;
    padding-bottom: <?= $THEME['borderwidth'] ?>;
    flex: 1 0 calc(25% - (4 * <?= $THEME['borderwidth'] ?>));
    background: <?= $THEME['altbg2'] ?>;
}

@media screen and (max-width: 850px) {
    .xmb-flex-4, .admin-panel .group {
        flex: 1 0 200px;
    }
}

.admin-attachment-search .row,
.admin-attachment-result .row,
.admin-censor .row,
.admin-email .row,
.admin-newsletter .row,
.admin-search-result .row,
.admin-settings .row {
    display: contents;
}

.xmb-grid-form-label,
.admin-attachment-search .label,
.admin-email .label,
.admin-newsletter .label,
.admin-settings .label {
    border: <?= $THEME['borderwidth'] ?> solid <?= $THEME['bordercolor'] ?>;
    padding: <?= $THEME['tablespace'] ?>;
    padding-top: calc(<?= $THEME['tablespace'] ?> + 2px);
    margin-right: -<?= $THEME['borderwidth'] ?>;
    margin-bottom: -<?= $THEME['borderwidth'] ?>;
    background: <?= $THEME['altbg1'] ?>;
}

.xmb-grid-form-field,
.admin-attachment-search .field,
.admin-attachment-result .cell,
.admin-censor .field,
.admin-content-row,
.admin-email .field,
.admin-newsletter .field,
.admin-search-form .field,
.admin-search-result .field,
.admin-search-result .row-head,
.admin-settings .field {
    border: <?= $THEME['borderwidth'] ?> solid <?= $THEME['bordercolor'] ?>;
    margin-right: -<?= $THEME['borderwidth'] ?>;
    margin-bottom: -<?= $THEME['borderwidth'] ?>;
    padding: <?= $THEME['tablespace'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    background: <?= $THEME['altbg2'] ?>;
}

.admin-content-row,
.admin-search-result .field {
    background: <?= $THEME['altbg1'] ?>;
}

.admin-settings .field {
    display: flex;
    align-items: center;
}

.category-head {
    <?= $THEME['catcss'] ?>
    color: <?= $THEME['cattext'] ?>;
    padding: <?= $THEME['tablespace'] ?>;
    font-weight: bold;
}

.admin-block > .category-head,
.admin-panel .category-head {
    text-align: center;
}

.admin-panel .category-head {
    border-bottom: <?= $THEME['borderwidth'] ?> solid <?= $THEME['bordercolor'] ?>;
}

.xmb-category-head-row-flex,
.admin-attachment-search .category-head,
.admin-attachment-result .category-head,
.admin-block > .category-head,
.admin-censor .category-head,
.admin-email .category-head,
.admin-newsletter .category-head,
.admin-search-form .category-head,
.admin-settings .category-head {
    border: <?= $THEME['borderwidth'] ?> solid <?= $THEME['bordercolor'] ?>;
    margin-right: -<?= $THEME['borderwidth'] ?>;
    margin-bottom: -<?= $THEME['borderwidth'] ?>;
    flex-basis: 100%;
}

.header, .header .cell {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-size: <?= $THEME['font-smaller-1'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.admin-attachment-search .span,
.admin-email .span,
.admin-newsletter .span,
.admin-settings .span {
    grid-column: 1 / span 2;
    display: block;
    text-align: center;
}

.admin-attachment-search .category-head,
.admin-email .category-head,
.admin-newsletter .category-head,
.admin-search-result .span,
.admin-settings .category-head {
    grid-column: 1 / span 2;
}

@media screen and (max-width: 600px) {
    .admin-settings input[type="text"],
    .admin-settings textarea {
        width: 250px;
    }
    .admin-attachment-search input[type="text"] {
        width: 190px;
    }
}

.admin-attachment-result .span {
    grid-column: 1 / span 6;
    text-align: center;
}

.admin-attachment-result .category-head {
    grid-column: 1 / span 6;
}

.admin-attachment-result .cell:nth-child(4),
.admin-attachment-result .cell:nth-child(5),
.admin-attachment-result .cell:nth-child(6),
.admin-censor .field:nth-child(1),
.admin-search-form .field {
    text-align: center;
}

@media screen and (max-width: 850px) {
    .admin-attachment-result {
        grid-template-columns: auto 20% 28% max-content;
    }
    .admin-attachment-result .cell:nth-child(4),
    .admin-attachment-result .cell:nth-child(6) {
        display: none;
    }
    .admin-attachment-result .span,
    .admin-attachment-result .category-head {
        grid-column: 1 / span 4;
    }
}

.admin-censor .span {
    grid-column: 1 / span 3;
    text-align: center;
}

@media screen and (max-width: 600px) {
    .admin-censor input[type="text"] {
        width: 150px;
    }
}

@media screen and (max-width: 600px) {
    .admin-email input[type="text"] {
        width: 250px;
    }
}

@media screen and (max-width: 800px) {
    .admin-newsletter input[type="text"],
    .admin-newsletter textarea {
        width: calc(100% - (2 * (<?= $THEME['tablespace'] ?> - <?= $THEME['borderwidth'] ?>)));
    }
}
