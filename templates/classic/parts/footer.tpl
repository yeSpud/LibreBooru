{if !isset($randomTip)}
    <div class="container mt-10">
        <div class="sub_menu" style="display:flex">
            <p class="m-0 p-0" style="color:white;">
                {replace s=$lang["footer_phrase"] n=["[OpenBooru]", "[version]", "[Aether]", "[other_contributors]"]
                r=['<a href="https://github.com/5ynchrogazer/OpenBooru" target="_blank" class="underline">OpenBooru</a>',
                    "v{$version}",
                '<a href="https://github.com/5ynchrogazer" target="_blank" class="underline">Aether</a>',
                '<a href="https://github.com/5ynchrogazer/OpenBooru/graphs/contributors" target="_blank"
                class="underline">'|cat:$lang["other_contributors"]|cat:'</a>']}
            </p>
            <span style="margin-left:auto;">
                <a href="javascript:void" id="localeSelector">Language</a>
            </span>
        </div>
    </div>
{/if}

<div class="toast_container" id="toast_container">
</div>

<script>
    const imgElement = document.getElementById("post_img");
    const videoElement = document.querySelector("video");

    function handleKeydown(event) {
        if (event.altKey && event.key === "s") {
            downloadMedia();
        } else if (event.key === "f" && !event.ctrlKey && !isInputOrTextarea(document.activeElement)) {
            toggleFullscreen();
        } else if ((event.key === "n" || (event.key === "ArrowLeft" && !event.altKey)) && !isInputOrTextarea(document
                .activeElement)) {
            clickNext();
        } else if ((event.key === "p" || (event.key === "ArrowRight" && !event.altKey)) && !isInputOrTextarea(document
                .activeElement)) {
            clickPrevious();
        } else if (event.key === "t" && !isInputOrTextarea(document.activeElement)) {
            toggleOriginal();
        } else if (event.key === "e" && !isInputOrTextarea(document.activeElement)) {
            toggleEditDiv();
        } else if (event.key === "c" && !isInputOrTextarea(document.activeElement)) {
            toggleCommentDiv();
        } else if (event.key === "h" && !isInputOrTextarea(document.activeElement)) {
            createToast("'H' displays this help message.<br>" +
                "'Alt+S' to download the media<br>" +
                "'F' to toggle fullscreen<br>" +
                "'N' or '<span title=\'Left Arrow\'>&larr;</span>' to view the next post<br>" +
                "'P' or '<span title=\'Righ Arrow\'>&rarr;</span>' to view the previous post<br>" +
                "'T' to toggle between cropped and original<br>" +
                "'O' to open the original image in a new tab<br>" +
                "'E' to toggle edit mode<br>" +
                "'C' to toggle comment field", "toast_info");
        } else if (event.key === "o" && !isInputOrTextarea(document.activeElement)) {
            openOriginalImage();
        }
    }

    function isInputOrTextarea(element) {
        return element.tagName === "INPUT" || element.tagName === "TEXTAREA";
    }

    if (imgElement || videoElement) {
        document.addEventListener("keydown", handleKeydown);
    }

    $(document).ready(function() {
        const localeSelector = $("#localeSelector");
        const localeSelectorMenu = $("<div>").addClass("localeSelectorMenu");
        for (const [key, value] of Object.entries(locales)) {
            const localeSelectorItem = $("<a>").attr("href", "javascript:void").text(value);
            localeSelectorItem.click(function() {
                document.cookie = "locale=" + key + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
                location.reload();
            });
            localeSelectorMenu.append(localeSelectorItem);
        }
        localeSelector.after(localeSelectorMenu);
        localeSelector.hover(function() {
            const offset = localeSelector.offset();
            const spaceBelow = $(window).height() - (offset.top + localeSelector.outerHeight());
            const spaceAbove = offset.top;
            if (spaceBelow > localeSelectorMenu.outerHeight()) {
                localeSelectorMenu.removeClass("top").addClass("bottom");
            } else {
                localeSelectorMenu.removeClass("bottom").addClass("top");
            }
            localeSelectorMenu.show();
        }, function() {
            localeSelectorMenu.hide();
        });
        localeSelectorMenu.hover(function() {
            $(this).show();
        }, function() {
            $(this).hide();
        });
    });
</script>

</body>

</html>