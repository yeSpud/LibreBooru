function tag_search(input) {
  var search = input.value;
  var tags = search.split(" ");
  var latestTag = tags[tags.length - 1];

  fetch(`/api/search.php?term=${latestTag}`)
    .then((response) => response.json())
    .then((data) => {
      //console.log(data);
      var dropdown = document.getElementById("tag-dropdown");
      if (data.error) {
        if (dropdown) {
          dropdown.remove();
          dropdown = null;
        }
        console.warn(data.error);
        return;
      }
      if (!dropdown) {
        dropdown = document.createElement("div");
        dropdown.id = "tag-dropdown";
        dropdown.style.position = "absolute";
        dropdown.style.backgroundColor = "#fff";
        dropdown.style.border = "1px solid #ccc";
        dropdown.style.zIndex = "1000";
        dropdown.style.textAlign = "left";
        dropdown.style.fontSize = "12px";
        dropdown.style.width = input.offsetWidth + "px";
        dropdown.style.top = input.offsetTop + input.offsetHeight + "px";
        dropdown.style.left = input.offsetLeft + "px";
        input.parentNode.appendChild(dropdown);
      }
      dropdown.innerHTML = "";
      data.forEach((tag) => {
        var item = document.createElement("div");
        item.textContent = tag.name + " (" + tag.count + ")";
        item.style.padding = "3px";
        item.style.cursor = "pointer";
        const colors = {
          copyright: color_copyright,
          character: color_character,
          artist: color_artist,
          general: color_general,
          meta: color_meta,
          other: color_other,
        };
        let color = colors[tag.category] || colors.other;
        item.style.color = color;
        item.addEventListener("click", () => {
          let prefix = latestTag.startsWith("-") ? "-" : "";
          input.value =
            input.value.substring(0, input.value.lastIndexOf(" ")) +
            " " +
            prefix +
            tag.name +
            " ";
          dropdown.innerHTML = "";
          input.focus();
        });
        dropdown.appendChild(item);
      });
      // Handle the data from the response
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function wiki_search(input) {
  var search = input.value;

  fetch(`/api/wiki_search.php?term=${search}`)
    .then((response) => response.json())
    .then((data) => {
      //console.log(data);
      var dropdown = document.getElementById("term-dropdown");
      if (data.error) {
        if (dropdown) dropdown.innerHTML = "";
        console.warn(data.error);
        return;
      }
      if (!dropdown) {
        dropdown = document.createElement("div");
        dropdown.id = "term-dropdown";
        dropdown.style.position = "absolute";
        dropdown.style.backgroundColor = "#fff";
        dropdown.style.border = "1px solid #ccc";
        dropdown.style.zIndex = "1000";
        dropdown.style.textAlign = "left";
        dropdown.style.fontSize = "12px";
        dropdown.style.width = input.offsetWidth + "px";
        dropdown.style.top = input.offsetTop + input.offsetHeight + "px";
        dropdown.style.left = input.offsetLeft + "px";
        input.parentNode.appendChild(dropdown);
      }
      dropdown.innerHTML = "";
      data.forEach((term) => {
        var item = document.createElement("div");
        item.textContent = term.term;
        item.style.padding = "3px";
        item.style.cursor = "pointer";
        const colors = {
          copyright: color_copyright,
          character: color_character,
          artist: color_artist,
          general: color_general,
          meta: color_meta,
          other: color_other,
        };
        let color = colors[term.category] || colors.other;
        item.style.color = color;
        item.addEventListener("click", () => {
          window.location.href = `/wiki.php?a=t&t=${term.term}`;
        });
        dropdown.appendChild(item);
      });
      // Handle the data from the response
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function getCookie(name) {
  var match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  if (match) return match[2];
}

function setCookie(name, value, days) {
  var date = new Date();
  date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
  document.cookie = name + "=" + value + "; expires=" + date.toUTCString();
}

function toggleOriginal() {
  var image = document.getElementById("post_img");
  var src = image.src;
  var isThumbnail = src.includes("/crops/");
  var original_src = isThumbnail
    ? src.replace("/crops/", "/images/")
    : src.replace("/images/", "/crops/");
  // Check first if resource exists on the webserver
  fetch(original_src)
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP error, status = " + response.status);
      }
      return response;
    })
    .then(() => {
      // Resource exists, switch image
      image.src = original_src;
      //console.log("Switched to", original_src);
      var span = document.getElementById("show-original");
      if (isThumbnail) {
        span.innerHTML = "here to hide";
      } else {
        span.innerHTML = "here to show";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      // Resource does not exist, do nothing
    });
}

function toggleAlwaysOriginal() {
  var showOriginal = getCookie("showOriginal");
  var text = document.getElementById("always-original");
  if (showOriginal) {
    setCookie("showOriginal", "", -1);
    text.innerHTML = "Always view original.";

    var image = document.getElementById("post_img");
    var src = image.src;
    var isThumbnail = src.includes("/images/");
    if (isThumbnail) {
      toggleOriginal();
    }
  } else {
    setCookie("showOriginal", "1", 365);
    text.innerHTML = "Always view cropped.";

    var image = document.getElementById("post_img");
    var src = image.src;
    var isThumbnail = src.includes("/crops/");
    if (isThumbnail) {
      toggleOriginal();
    }
  }
}

function toggleHideOriginalMessage() {
  var hideOriginalMessage = getCookie("hideOriginalMessage");
  if (hideOriginalMessage) {
    setCookie("hideOriginalMessage", "", -1);
  } else {
    setCookie("hideOriginalMessage", "1", 365);
    // Remove the message from the page
    var message = document.getElementById("original-message");
    message.remove();
  }
}

function scrollTo(id) {
  const element = document.getElementById(id);
  if (!element) {
    console.warn(`Element with id ${id} not found.`);
    return;
  }
  const y = element.getBoundingClientRect().top + window.scrollY;
  window.scroll({
    top: y,
    behavior: "smooth",
  });
}

function toggleEditDiv() {
  toggleDiv("edit-div");
  scrollTo("edit-div");
  hideDiv("comment-div");
}

function toggleCommentDiv() {
  toggleDiv("comment-div");
  scrollTo("comment-div");
  hideDiv("edit-div");
}

function toggleDiv(id) {
  var div = document.getElementById(id);
  if (!div) {
    console.warn(`Element with id ${id} not found.`);
    return;
  }
  if (div.style.display === "none") {
    div.style.display = "block";
  } else {
    div.style.display = "none";
  }
}

function hideDiv(id) {
  var div = document.getElementById(id);
  if (!div) {
    console.warn(`Element with id ${id} not found.`);
    return;
  }
  div.style.display = "none";
}

function downloadMedia() {
  const imgElement = document.getElementById("post_img");
  const videoElement = document.getElementById("post_video");
  if (imgElement) {
    const link = document.createElement("a");
    const originalSrc = imgElement.src.replace("/crops/", "/images/");
    link.href = originalSrc;
    link.download = originalSrc.split("/").pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } else if (videoElement) {
    const sourceElement = videoElement.querySelector("source");
    if (sourceElement) {
      const link = document.createElement("a");
      link.href = sourceElement.src;
      link.setAttribute("download", "");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
}

function toggleFullscreen() {
  var video = document.getElementById("post_video");
  if (!video) video = document.getElementById("post_img");
  if (!video) {
    console.warn("No media element found.");
    return;
  }

  if (!document.fullscreenElement) {
    if (video.requestFullscreen) {
      video.requestFullscreen();
    } else if (video.mozRequestFullScreen) {
      // Firefox
      video.mozRequestFullScreen();
    } else if (video.webkitRequestFullscreen) {
      // Chrome, Safari and Opera
      video.webkitRequestFullscreen();
    } else if (video.msRequestFullscreen) {
      // IE/Edge
      video.msRequestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.mozCancelFullScreen) {
      // Firefox
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      // Chrome, Safari and Opera
      document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
      // IE/Edge
      document.msExitFullscreen();
    }
  }
}

function clickPrevious() {
  var previousLink = document.getElementById("previousPost");
  if (previousLink) {
    previousLink.click();
  }
}

function clickNext() {
  var nextLink = document.getElementById("nextPost");
  if (nextLink) {
    nextLink.click();
  }
}

function openOriginalImage() {
  var link = document.getElementById("originalImageLink");
  if (link) {
    link.click();
  }
}

function createToast(content, cl = "") {
  const container = document.getElementById("toast_container");
  if (!container) {
    console.warn("Toast container not found.");
    return;
  }
  var toast = document.createElement("div");
  toast.className = "toast " + cl;
  toast.innerHTML = content;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = 0;
    setTimeout(() => {
      toast.remove();
    }, 1000);
  }, 5000);
}

function deletePost(id) {
  var confirmation = confirm("Are you sure you want to delete this post?");
  if (confirmation) {
    var reason = prompt("Please enter the reason for deletion:");
    if (reason) {
      fetch(`/api/delete_post.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${encodeURIComponent(id)}&reason=${encodeURIComponent(
          reason
        )}`,
      })
        .then((response) => response.json())
        .then((data) => {
          console.log(data);
          if (data.success) {
            createToast("Post deleted successfully.", "toast_success");
            setTimeout(() => {
              location.reload();
            }, 2000);
            // Optionally, redirect or update the UI
          } else {
            createToast("Failed to delete post: " + data.error, "toast_error");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          createToast(
            "An error occurred while deleting the post.",
            "toast_error"
          );
        });
    } else {
      createToast("Deletion cancelled. Reason is required.", "toast_warning");
    }
  }
}

function restorePost(id) {
  var confirmation = confirm("Are you sure you want to restore this post?");
  if (confirmation) {
    fetch(`/api/restore_post.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.success) {
          createToast("Post restored successfully.", "toast_success");
          setTimeout(() => {
            location.reload();
          }, 2000);
          // Optionally, redirect or update the UI
        } else {
          createToast("Failed to restore post: " + data.error, "toast_error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        createToast(
          "An error occurred while restoring the post.",
          "toast_error"
        );
      });
  }
}

function approvePost(id) {
  var confirmation = confirm("Are you sure you want to approve this post?");
  if (confirmation) {
    fetch(`/api/approve_post.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.success) {
          createToast("Post approved successfully.", "toast_success");
          setTimeout(() => {
            location.reload();
          }, 2000);
          // Optionally, redirect or update the UI
        } else {
          createToast("Failed to approve post: " + data.error, "toast_error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        createToast(
          "An error occurred while approving the post.",
          "toast_error"
        );
      });
  }
}

function votePost(id, action) {
  // Can only be "up", "down", or "remove"
  if (!["up", "down", "remove"].includes(action)) {
    createToast("Invalid vote action.", "toast_error");
    return;
  }
  fetch(`/api/vote_post.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${encodeURIComponent(id)}&action=${encodeURIComponent(action)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      if (data.success) {
        createToast(data.message, "toast_success");
        // Update vote count span id = postScore and math the vote count
        var score = document.getElementById("postScore");
        if (score) {
          // data.vote is either 1, -1, or 0, math that to the current score
          var currentScore = parseInt(score.innerHTML);
          score.innerHTML = currentScore + data.vote;
        }
        // Optionally, update the UI
      } else {
        createToast("Failed to submit vote: " + data.error, "toast_error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      createToast(
        "An error occurred while submitting the vote.",
        "toast_error"
      );
    });
}

function voteComment(id, action) {
  // Can only be "up", "down", or "remove"
  if (!["up", "down", "remove"].includes(action)) {
    createToast("Invalid vote action.", "toast_error");
    return;
  }
  fetch(`/api/vote_comment.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${encodeURIComponent(id)}&action=${encodeURIComponent(action)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      if (data.success) {
        createToast(data.message, "toast_success");
        // Update vote count span id = postScore and math the vote count
        var score = document.getElementById("commentScore" + id);
        if (score) {
          // data.vote is either 1, -1, or 0, math that to the current score
          var currentScore = parseInt(score.innerHTML);
          score.innerHTML = currentScore + data.vote;
        }
        // Optionally, update the UI
      } else {
        createToast("Failed to submit vote: " + data.error, "toast_error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      createToast(
        "An error occurred while submitting the vote.",
        "toast_error"
      );
    });
}

function reportPost(id) {
  var reason = prompt("Please enter the reason for reporting this post:");
  if (reason) {
    fetch(`/api/report_post.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}&reason=${encodeURIComponent(reason)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.success) {
          createToast("Post reported successfully.", "toast_success");
          setTimeout(() => {
            location.reload();
          }, 2000);
        } else {
          createToast("Failed to report post: " + data.error, "toast_error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        createToast(
          "An error occurred while reporting the post.",
          "toast_error"
        );
      });
  } else {
    createToast("Reporting cancelled. Reason is required.", "toast_warning");
  }
}

function reportComment(id) {
  var reason = prompt("Please enter the reason for reporting this comment:");
  if (reason) {
    fetch(`/api/report_comment.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}&reason=${encodeURIComponent(reason)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.success) {
          createToast("Comment reported successfully.", "toast_success");
          setTimeout(() => {
            location.reload();
          }, 2000);
        } else {
          createToast("Failed to report comment: " + data.error, "toast_error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        createToast(
          "An error occurred while reporting the comment.",
          "toast_error"
        );
      });
  } else {
    createToast("Reporting cancelled. Reason is required.", "toast_warning");
  }
}

function toggleFavourite(id) {
  fetch(`/api/favourite_post.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${encodeURIComponent(id)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      if (data.success) {
        createToast(data.message, "toast_success");
        let favouriteText = document.getElementById("favouriteText");
        if (data.message == "Post added to favourites") {
          votePost(id, "up");
          favouriteText.innerHTML = "Remove from Favourites";
        } else {
          favouriteText.innerHTML = "Add to Favourites";
        }
        // Optionally, update the UI
      } else {
        createToast("Failed to favourite post: " + data.error, "toast_error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      createToast(
        "An error occurred while favouriting the post.",
        "toast_error"
      );
    });
}

function approveReport(id, post, type = "post") {
  var confirmation = confirm("Are you sure you want to approve this report?");
  var reportDiv = document.getElementById("report-" + id);
  if (confirmation) {
    var reason = prompt("Please enter the reason for approving this report:");
    if (reason) {
      let url = "";
      if (type == "post") {
        url = `/api/delete_post.php`;
      } else {
        url = `/api/delete_comment.php`;
      }
      fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${encodeURIComponent(post)}&reason=${encodeURIComponent(
          reason
        )}`,
      })
        .then((response) => response.json())
        .then((data) => {
          console.log(data);
          if (data.success) {
            if (type == "post") {
              createToast("Post deleted successfully.", "toast_success");
            } else {
              createToast("Comment deleted successfully.", "toast_success");
            }
            url = "";
            if (type == "post") {
              url = `/api/approve_report.php`;
            } else {
              url = `/api/approve_comment_report.php`;
            }
            fetch(url, {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
              body: `id=${encodeURIComponent(id)}`,
            })
              .then((response) => response.json())
              .then((data) => {
                console.log(data);
                if (data.success) {
                  createToast("Report approved successfully.", "toast_success");
                  if (reportDiv) {
                    reportDiv.remove();
                  } else {
                    // Timeout 2s
                    setTimeout(() => {
                      location.reload();
                    }, 2000);
                  }
                } else {
                  createToast(
                    "Failed to approve report: " + data.error,
                    "toast_error"
                  );
                }
              })
              .catch((error) => {
                console.error("Error:", error);
                createToast(
                  "An error occurred while approving the report.",
                  "toast_error"
                );
              });
            // Optionally, redirect or update the UI
          } else {
            if (type == "post") {
              createToast(
                "Failed to delete post: " + data.error,
                "toast_error"
              );
            } else {
              createToast(
                "Failed to delete comment: " + data.error,
                "toast_error"
              );
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          if (type == "post") {
            createToast(
              "An error occurred while deleting the post.",
              "toast_error"
            );
          } else {
            createToast(
              "An error occurred while deleting the comment.",
              "toast_error"
            );
          }
        });
    } else {
      createToast("Approval cancelled. Reason is required.", "toast_warning");
    }
  }
}

function rejectReport(id) {
  var confirmation = confirm("Are you sure you want to reject this report?");
  var reportDiv = document.getElementById("report-" + id);
  if (confirmation) {
    fetch(`/api/reject_report.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.success) {
          createToast("Report rejected successfully.", "toast_success");
          if (reportDiv) {
            reportDiv.remove();
          } else {
            // Timeout 2s
            setTimeout(() => {
              location.reload();
            }, 2000);
          }
        } else {
          createToast("Failed to reject report: " + data.error, "toast_error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        createToast(
          "An error occurred while rejecting the report.",
          "toast_error"
        );
      });
  }
}

function rejectReport(id, type = "post") {
  var confirmation = confirm("Are you sure you want to reject this report?");
  var reportDiv = document.getElementById("report-" + id);
  if (confirmation) {
    let url = "";
    if (type == "post") {
      url = `/api/reject_report.php`;
    } else {
      url = `/api/reject_comment_report.php`;
    }
    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(id)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.success) {
          createToast("Report rejected successfully.", "toast_success");
          if (reportDiv) {
            reportDiv.remove();
            if (type == "comment") {
              fetch(`/api/restore_comment.php`, {
                method: "POST",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `id=${encodeURIComponent(id)}`,
              })
                .then((response) => response.json())
                .then((data) => {
                  console.log(data);
                  if (data.success) {
                    createToast(
                      "Comment restored successfully.",
                      "toast_success"
                    );
                    if (reportDiv) {
                      reportDiv.remove();
                    } else {
                      // Timeout 2s
                      setTimeout(() => {
                        location.reload();
                      }, 2000);
                    }
                  } else {
                    createToast(
                      "Failed to restore comment: " + data.error,
                      "toast_error"
                    );
                  }
                })
                .catch((error) => {
                  console.error("Error:", error);
                  createToast(
                    "An error occurred while rejecting the report.",
                    "toast_error"
                  );
                });
            }
          } else {
            // Timeout 2s
            setTimeout(() => {
              location.reload();
            }, 2000);
          }
        } else {
          createToast("Failed to reject report: " + data.error, "toast_error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        createToast(
          "An error occurred while rejecting the report.",
          "toast_error"
        );
      });
  }
}
